<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Services\WorkOrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function __construct(private readonly WorkOrderNotifier $notifier) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('materials', 'sku')],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $material = DB::transaction(function () use ($request, $validated): Material {
            $material = Material::create($validated);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'material_registered',
                'record_type' => Material::class,
                'record_id' => $material->id,
                'details' => 'Registered material '.$material->sku.' ('.$material->name.') with '.$material->quantity_on_hand.' '.$material->unit.' on hand.',
            ]);

            return $material;
        });

        if ($material->isLowStock()) {
            $this->notifier->lowInventory($material);
        }

        return redirect()->route('operations.dashboard')->with('status', 'Material registered in inventory.');
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $wasLowStock = $material->isLowStock();

        $material = DB::transaction(function () use ($request, $material, $validated): Material {
            $material->update($validated);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'material_updated',
                'record_type' => Material::class,
                'record_id' => $material->id,
                'details' => 'Updated material '.$material->sku.' to '.$material->quantity_on_hand.' '.$material->unit.' on hand with a reorder level of '.$material->reorder_level.'.',
            ]);

            return $material;
        });

        if (! $wasLowStock && $material->isLowStock()) {
            $this->notifier->lowInventory($material);
        }

        return redirect()->route('operations.dashboard')->with('status', 'Inventory record updated.');
    }
}
