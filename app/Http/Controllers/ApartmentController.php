<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApartmentController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $search = trim((string) $request->query('search', ''));

        $apartments = Apartment::query()
            ->with(['buildingBlock', 'activeResident'])
            ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->where('building_block_id', $blockId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('activeResident', fn ($resident) => $resident->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('buildingBlock', fn ($block) => $block->where('name', 'like', "%{$search}%"));
                });
            })
            ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
            ->orderBy('building_blocks.name')
            ->orderBy('apartments.number')
            ->select('apartments.*')
            ->paginate(12)
            ->withQueryString();

        $stats = Apartment::query()
            ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->where('building_block_id', $blockId))
            ->selectRaw('COUNT(*) as total, SUM(status = "occupied") as occupied, SUM(status = "empty") as empty_count')
            ->first();

        return view('apartments.index', compact('site', 'sites', 'blocks', 'blockId', 'apartments', 'stats', 'search'));
    }

    public function create(): View
    {
        return view('apartments.create', [
            'site' => Site::query()->firstOrFail(),
            'blocks' => BuildingBlock::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'building_block_id' => ['required', 'exists:building_blocks,id'],
            'number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('apartments')->where(fn ($query) => $query->where('building_block_id', $request->integer('building_block_id'))),
            ],
            'floor_no' => ['nullable', 'integer', 'between:0,200'],
            'status' => ['required', 'in:occupied,empty'],
            'resident_full_name' => ['nullable', 'string', 'max:160'],
            'resident_phone' => ['nullable', 'string', 'max:30'],
            'resident_email' => ['nullable', 'email', 'max:160'],
            'resident_type' => ['nullable', 'in:owner,tenant'],
        ]);

        $apartment = Apartment::create([
            'building_block_id' => $validated['building_block_id'],
            'number' => $validated['number'],
            'floor_no' => $validated['floor_no'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['resident_full_name'])) {
            Resident::create([
                'apartment_id' => $apartment->id,
                'full_name' => $validated['resident_full_name'],
                'phone' => $validated['resident_phone'] ?? null,
                'email' => $validated['resident_email'] ?? null,
                'resident_type' => $validated['resident_type'] ?? 'owner',
                'is_active' => true,
            ]);

            $apartment->update(['status' => 'occupied']);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_apartment',
            'table_name' => 'apartments',
            'record_id' => $apartment->id,
            'ip_address' => $request->ip(),
            'description' => "{$apartment->number} dairesi oluşturuldu.",
        ]);

        return redirect()->route('apartments.index')->with('status', 'Daire kaydı oluşturuldu.');
    }

    public function edit(Apartment $apartment): View
    {
        return view('apartments.edit', [
            'apartment' => $apartment->load(['buildingBlock', 'activeResident']),
            'blocks' => BuildingBlock::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Apartment $apartment): RedirectResponse
    {
        $validated = $request->validate([
            'building_block_id' => ['required', 'exists:building_blocks,id'],
            'number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('apartments')
                    ->where(fn ($query) => $query->where('building_block_id', $request->integer('building_block_id')))
                    ->ignore($apartment->id),
            ],
            'floor_no' => ['nullable', 'integer', 'between:0,200'],
            'status' => ['required', 'in:occupied,empty'],
        ]);

        $apartment->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_apartment',
            'table_name' => 'apartments',
            'record_id' => $apartment->id,
            'ip_address' => $request->ip(),
            'description' => "{$apartment->number} dairesi güncellendi.",
        ]);

        return redirect()->route('apartments.index')->with('status', 'Daire güncellendi.');
    }

    public function destroy(Request $request, Apartment $apartment): RedirectResponse
    {
        if ($apartment->dues()->exists()) {
            return back()->withErrors(['delete' => 'Bu dairede aidat kaydı var. Önce aidat kayıtlarını silmelisiniz.']);
        }

        $number = $apartment->number;
        $apartment->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_apartment',
            'table_name' => 'apartments',
            'ip_address' => $request->ip(),
            'description' => "{$number} dairesi silindi.",
        ]);

        return redirect()->route('apartments.index')->with('status', 'Daire silindi.');
    }
}
