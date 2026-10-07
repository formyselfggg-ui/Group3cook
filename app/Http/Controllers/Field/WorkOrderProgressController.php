<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterialUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WorkOrderProgressController extends Controller
{
    public function start(Request $request, int $workOrder): RedirectResponse
    {
        $assignedWorkOrder = $this->assignedWorkOrder($request, $workOrder);

        if ($assignedWorkOrder->status !== WorkOrder::STATUS_ASSIGNED) {
            return back()->withErrors([
                'work_order' => 'Only assigned work orders can be started.',
            ]);
        }

        $started = DB::transaction(function () use ($request, $assignedWorkOrder): bool {
            $currentWorkOrder = WorkOrder::query()
                ->whereBelongsTo($request->user(), 'assignedPersonnel')
                ->whereKey($assignedWorkOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentWorkOrder->status !== WorkOrder::STATUS_ASSIGNED) {
                return false;
            }

            $currentWorkOrder->update(['status' => WorkOrder::STATUS_IN_PROGRESS]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_started',
                'record_type' => WorkOrder::class,
                'record_id' => $currentWorkOrder->id,
                'details' => 'Field personnel started work order '.$currentWorkOrder->work_order_number.'.',
            ]);

            return true;
        });

        if (! $started) {
            return back()->withErrors([
                'work_order' => 'Only assigned work orders can be started.',
            ]);
        }

        return redirect()->route('field.dashboard')->with('status', 'Work started. Keep the task updated as you go.');
    }

    public function submit(Request $request, int $workOrder): RedirectResponse
    {
        $assignedWorkOrder = $this->assignedWorkOrder($request, $workOrder);

        if ($assignedWorkOrder->status !== WorkOrder::STATUS_IN_PROGRESS) {
            return back()->withErrors([
                'work_order' => 'Only in-progress work orders can be submitted for review.',
            ]);
        }

        $validated = $request->validate([
            'work_performed' => ['required', 'string', 'max:10000'],
            'field_notes' => ['nullable', 'string', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5120)],
        ]);

        $submitted = DB::transaction(function () use ($request, $assignedWorkOrder, $validated): bool {
            $currentWorkOrder = WorkOrder::query()
                ->whereBelongsTo($request->user(), 'assignedPersonnel')
                ->whereKey($assignedWorkOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentWorkOrder->status !== WorkOrder::STATUS_IN_PROGRESS) {
                return false;
            }

            $photoPaths = collect($validated['photos'] ?? [])
                ->map(fn (UploadedFile $photo): string => $photo->store(
                    "work-orders/{$currentWorkOrder->id}",
                    'local',
                ))
                ->all();

            $currentWorkOrder->update([
                'status' => WorkOrder::STATUS_FOR_REVIEW,
                'work_performed' => $validated['work_performed'],
                'field_notes' => $validated['field_notes'] ?? null,
                'return_notes' => null,
                'photo_paths' => array_merge($currentWorkOrder->photo_paths ?? [], $photoPaths),
                'submitted_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_submitted_for_review',
                'record_type' => WorkOrder::class,
                'record_id' => $currentWorkOrder->id,
                'details' => 'Submitted work order '.$currentWorkOrder->work_order_number.' for supervisor review.',
            ]);

            return true;
        });

        if (! $submitted) {
            return back()->withErrors([
                'work_order' => 'Only in-progress work orders can be submitted for review.',
            ]);
        }

        return redirect()->route('field.dashboard')->with(
            'status',
            'Work submitted for supervisor review.',
        );
    }

    public function recordMaterial(Request $request, int $workOrder): RedirectResponse
    {
        $assignedWorkOrder = $this->assignedWorkOrder($request, $workOrder);

        if ($assignedWorkOrder->status !== WorkOrder::STATUS_IN_PROGRESS) {
            return back()->withErrors([
                'work_order' => 'Materials can only be recorded on in-progress work orders.',
            ]);
        }

        $validated = $request->validate([
            'material_id' => ['required', 'integer', Rule::exists('materials', 'id')],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ]);

        $recorded = DB::transaction(function () use ($request, $assignedWorkOrder, $validated): bool {
            $currentWorkOrder = WorkOrder::query()
                ->whereBelongsTo($request->user(), 'assignedPersonnel')
                ->whereKey($assignedWorkOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentWorkOrder->status !== WorkOrder::STATUS_IN_PROGRESS) {
                return false;
            }

            $material = Material::query()
                ->whereKey($validated['material_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $material->quantity_on_hand < (float) $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$material->quantity_on_hand} {$material->unit} available in inventory.",
                ]);
            }

            $stockUpdated = Material::query()
                ->whereKey($material->id)
                ->where('quantity_on_hand', '>=', $validated['quantity'])
                ->decrement('quantity_on_hand', $validated['quantity']);

            if ($stockUpdated !== 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'There is not enough material available in inventory.',
                ]);
            }

            WorkOrderMaterialUsage::create([
                'work_order_id' => $assignedWorkOrder->id,
                'material_id' => $material->id,
                'user_id' => $request->user()->id,
                'quantity' => $validated['quantity'],
                'used_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_material_recorded',
                'record_type' => WorkOrder::class,
                'record_id' => $currentWorkOrder->id,
                'details' => "Recorded {$validated['quantity']} {$material->unit} of {$material->name} for work order {$currentWorkOrder->work_order_number}.",
            ]);

            return true;
        });

        if (! $recorded) {
            return back()->withErrors([
                'work_order' => 'Materials can only be recorded on in-progress work orders.',
            ]);
        }

        return redirect()->route('field.dashboard')->with('status', 'Material usage recorded and inventory updated.');
    }

    private function assignedWorkOrder(Request $request, int $workOrder): WorkOrder
    {
        return WorkOrder::query()
            ->whereBelongsTo($request->user(), 'assignedPersonnel')
            ->whereKey($workOrder)
            ->firstOrFail();
    }
}
