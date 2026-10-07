<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkOrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::in(array_keys(WorkOrder::PRIORITIES))],
            'assigned_personnel_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_FIELD_PERSONNEL)->where('is_active', true),
            ],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $workOrder = WorkOrder::create([
                'work_order_number' => 'TEMP-'.Str::upper(Str::random(16)),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'assigned_personnel_id' => $validated['assigned_personnel_id'],
                'asset_id' => $validated['asset_id'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'date_assigned' => now(),
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => WorkOrder::STATUS_ASSIGNED,
            ]);

            $workOrder->update([
                'work_order_number' => 'WO-'.str_pad((string) $workOrder->id, 6, '0', STR_PAD_LEFT),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_created',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => 'Created work order '.$workOrder->work_order_number.'.',
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'field_personnel_assigned',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => 'Assigned work order '.$workOrder->work_order_number.' to '.$workOrder->assignedPersonnel->name.'.',
            ]);
        });

        return redirect()->route('supervisor.dashboard')->with('status', 'Work order created and assigned.');
    }

    public function review(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'return'])],
            'return_notes' => ['nullable', 'required_if:decision,return', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $workOrder, $validated): void {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);

            if ($workOrder->status !== WorkOrder::STATUS_FOR_REVIEW) {
                throw ValidationException::withMessages([
                    'work_order' => 'Only work orders waiting for review can be reviewed.',
                ]);
            }

            if ($validated['decision'] === 'approve') {
                $workOrder->update([
                    'supervisor_approved_by_id' => $request->user()->id,
                    'supervisor_approved_at' => now(),
                    'return_notes' => null,
                ]);

                $action = 'work_order_approved';
                $details = 'Approved work order '.$workOrder->work_order_number.' for closure.';
            } else {
                $workOrder->update([
                    'status' => WorkOrder::STATUS_IN_PROGRESS,
                    'supervisor_approved_by_id' => null,
                    'supervisor_approved_at' => null,
                    'submitted_at' => null,
                    'return_notes' => $validated['return_notes'],
                ]);

                $action = 'work_order_returned_for_correction';
                $details = 'Returned work order '.$workOrder->work_order_number.' to assigned field personnel: '.$validated['return_notes'];
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $action,
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => $details,
            ]);
        });

        $message = $validated['decision'] === 'approve'
            ? 'Work approved. Close the work order when ready.'
            : 'Work returned to field personnel with correction instructions.';

        return redirect()->route('supervisor.dashboard')->with('status', $message);
    }

    public function close(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $workOrder): void {
            $workOrder = WorkOrder::query()->with('asset')->lockForUpdate()->findOrFail($workOrder->id);

            if (
                $workOrder->status !== WorkOrder::STATUS_FOR_REVIEW
                || $workOrder->supervisor_approved_at === null
            ) {
                throw ValidationException::withMessages([
                    'work_order' => 'A work order must be approved before it can be closed.',
                ]);
            }

            $workOrder->update([
                'status' => WorkOrder::STATUS_COMPLETED,
                'completion_at' => now(),
            ]);

            if ($workOrder->asset !== null && $workOrder->asset->current_status === 'under_maintenance') {
                $workOrder->asset->update(['current_status' => 'active']);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_closed',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => 'Closed approved work order '.$workOrder->work_order_number.'.',
            ]);
        });

        return redirect()->route('supervisor.dashboard')->with('status', 'Work order closed.');
    }

    public function photo(WorkOrder $workOrder, int $photo): BinaryFileResponse
    {
        $path = $workOrder->photo_paths[$photo] ?? null;

        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
}
