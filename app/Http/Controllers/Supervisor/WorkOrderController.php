<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderNotifier;
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
    public function __construct(private readonly WorkOrderNotifier $notifier) {}

    public function assign(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_personnel_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_FIELD_PERSONNEL)->where('is_active', true),
            ],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $assignedWorkOrder = DB::transaction(function () use ($request, $workOrder, $validated): WorkOrder {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);

            if ($workOrder->status !== WorkOrder::STATUS_PENDING || $workOrder->assigned_personnel_id !== null) {
                throw ValidationException::withMessages([
                    'work_order' => 'Only unassigned pending work requirements can be assigned.',
                ]);
            }

            $workOrder->update([
                'assigned_personnel_id' => $validated['assigned_personnel_id'],
                'date_assigned' => now(),
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => WorkOrder::STATUS_ASSIGNED,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'field_personnel_assigned',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => 'Assigned work order '.$workOrder->work_order_number.' to '.$workOrder->assignedPersonnel->name.'.',
            ]);

            return $workOrder;
        });

        $this->notifier->assigned($assignedWorkOrder);

        return redirect()->route('supervisor.dashboard')
            ->with('status', 'Work requirement assigned to field personnel.');
    }

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

        $createdWorkOrder = DB::transaction(function () use ($request, $validated): WorkOrder {
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

            return $workOrder;
        });

        $this->notifier->assigned($createdWorkOrder);

        return redirect()->route('supervisor.dashboard')->with('status', 'Work order created and assigned.');
    }

    public function priority(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', Rule::in(array_keys(WorkOrder::PRIORITIES))],
        ]);

        $updatedWorkOrder = DB::transaction(function () use ($request, $workOrder, $validated): WorkOrder {
            $workOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);

            if (! in_array($workOrder->status, WorkOrder::DELETABLE_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'work_order' => 'Priority can only be changed before field personnel start the work.',
                ]);
            }

            $previousPriority = $workOrder->priority;

            if ($previousPriority !== $validated['priority']) {
                $workOrder->update(['priority' => $validated['priority']]);

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'work_order_priority_changed',
                    'record_type' => WorkOrder::class,
                    'record_id' => $workOrder->id,
                    'details' => 'Changed priority of work order '.$workOrder->work_order_number.' from '.$previousPriority.' to '.$validated['priority'].'.',
                ]);
            }

            return $workOrder;
        });

        $this->notifier->priorityChanged($updatedWorkOrder, (string) $updatedWorkOrder->getOriginal('priority'));

        return redirect()->route('supervisor.dashboard')->with('status', 'Work order priority updated.');
    }

    public function review(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'return'])],
            'return_notes' => ['nullable', 'required_if:decision,return', 'string', 'max:5000'],
        ]);

        $reviewedWorkOrder = DB::transaction(function () use ($request, $workOrder, $validated): WorkOrder {
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

            return $workOrder;
        });

        if ($validated['decision'] === 'approve') {
            $this->notifier->approved($reviewedWorkOrder);
        } else {
            $this->notifier->returnedForCorrection($reviewedWorkOrder, $validated['return_notes']);
        }

        $message = $validated['decision'] === 'approve'
            ? 'Work approved. Close the work order when ready.'
            : 'Work returned to field personnel with correction instructions.';

        return redirect()->route('supervisor.dashboard')->with('status', $message);
    }

    public function close(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $closedWorkOrder = DB::transaction(function () use ($request, $workOrder): WorkOrder {
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

            return $workOrder;
        });

        $this->notifier->completed($closedWorkOrder);

        return redirect()->route('supervisor.dashboard')->with('status', 'Work order closed.');
    }

    public function destroy(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $workOrder): void {
            $workOrder = WorkOrder::query()->with('materialUsages')->lockForUpdate()->findOrFail($workOrder->id);

            if (! $workOrder->canBeDeleted()) {
                throw ValidationException::withMessages([
                    'work_order' => 'Only work orders that field personnel have not started can be deleted.',
                ]);
            }

            foreach ($workOrder->photo_paths ?? [] as $path) {
                Storage::disk('local')->delete($path);
            }

            $workOrder->materialUsages()->delete();

            ActivityLog::query()
                ->where('record_type', WorkOrder::class)
                ->where('record_id', $workOrder->id)
                ->delete();

            $details = 'Deleted work order '.$workOrder->work_order_number.' ('.$workOrder->title.')'
                .($workOrder->assignedPersonnel?->name !== null
                    ? ' previously assigned to '.$workOrder->assignedPersonnel->name
                    : ' that was not yet assigned')
                .'.';

            $workOrderId = $workOrder->id;
            $workOrder->delete();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_deleted',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrderId,
                'details' => $details,
            ]);
        });

        return redirect()->route('supervisor.dashboard')
            ->with('status', 'Work order deleted.');
    }

    public function photo(WorkOrder $workOrder, int $photo): BinaryFileResponse
    {
        $path = $workOrder->photo_paths[$photo] ?? null;

        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
}
