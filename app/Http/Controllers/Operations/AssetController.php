<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_number' => ['required', 'string', 'max:255', 'unique:assets,asset_number'],
            'asset_type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'current_status' => ['required', Rule::in(array_keys(Asset::STATUSES))],
            'condition' => ['nullable', 'string', 'max:255'],
            'installed_on' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $asset = Asset::create($validated);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'asset_created',
                'record_type' => Asset::class,
                'record_id' => $asset->id,
                'details' => 'Registered asset '.$asset->asset_number.'.',
            ]);
        });

        return redirect()->route('operations.dashboard', [], 303)
            ->with('status', 'Asset registered successfully.');
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'asset_number' => ['required', 'string', 'max:255', Rule::unique('assets', 'asset_number')->ignore($asset->id)],
            'asset_type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'current_status' => ['required', Rule::in(array_keys(Asset::STATUSES))],
            'condition' => ['nullable', 'string', 'max:255'],
            'installed_on' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request, $asset, $validated): void {
            $asset->update($validated);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'asset_updated',
                'record_type' => Asset::class,
                'record_id' => $asset->id,
                'details' => 'Updated asset '.$asset->asset_number.'.',
            ]);
        });

        return redirect()->route('operations.dashboard', [], 303)
            ->with('status', 'Asset record updated.');
    }
}
