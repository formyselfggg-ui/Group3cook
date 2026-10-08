<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::in(array_keys(WorkOrder::PRIORITIES))],
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
                'asset_id' => $validated['asset_id'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => WorkOrder::STATUS_PENDING,
            ]);

            $workOrder->update([
                'work_order_number' => 'WO-'.str_pad((string) $workOrder->id, 6, '0', STR_PAD_LEFT),
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'work_order_created',
                'record_type' => WorkOrder::class,
                'record_id' => $workOrder->id,
                'details' => 'Registered work requirement '.$workOrder->work_order_number.'.',
            ]);
        });

        return redirect()->route('operations.dashboard', [], 303)
            ->with('status', 'Work requirement registered for supervisor assignment.');
    }
}
