<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResidentController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $search = trim((string) $request->query('search', ''));

        $residents = Resident::query()
            ->with(['apartment.buildingBlock'])
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('apartment', fn ($apartment) => $apartment->where('number', 'like', "%{$search}%"));
                });
            })
            ->latest('is_active')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('residents.index', compact('site', 'sites', 'blocks', 'blockId', 'residents', 'search'));
    }

    public function create(): View
    {
        $site = Site::query()->firstOrFail();

        return view('residents.create', [
            'site' => $site,
            'apartments' => Apartment::query()
                ->with(['buildingBlock', 'activeResident'])
                ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
                ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
                ->orderBy('building_blocks.name')
                ->orderBy('apartments.number')
                ->select('apartments.*')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'exists:apartments,id'],
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'resident_type' => ['required', 'in:owner,tenant'],
        ]);

        $resident = DB::transaction(function () use ($validated) {
            Resident::query()
                ->where('apartment_id', $validated['apartment_id'])
                ->update(['is_active' => false]);

            $resident = Resident::create($validated + ['is_active' => true]);
            $resident->apartment()->update(['status' => 'occupied']);

            return $resident;
        });

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_resident',
            'table_name' => 'residents',
            'record_id' => $resident->id,
            'ip_address' => $request->ip(),
            'description' => "{$resident->full_name} sakin olarak eklendi.",
        ]);

        return redirect()->route('residents.index')->with('status', 'Sakin kaydı oluşturuldu.');
    }

    public function edit(Resident $resident): View
    {
        $site = Site::query()->firstOrFail();

        return view('residents.edit', [
            'resident' => $resident->load('apartment.buildingBlock'),
            'apartments' => Apartment::query()
                ->with(['buildingBlock', 'activeResident'])
                ->whereHas('buildingBlock', fn ($query) => $query->where('site_id', $site->id))
                ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
                ->orderBy('building_blocks.name')
                ->orderBy('apartments.number')
                ->select('apartments.*')
                ->get(),
        ]);
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $validated = $request->validate([
            'apartment_id' => ['required', 'exists:apartments,id'],
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'resident_type' => ['required', 'in:owner,tenant'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($resident, $validated) {
            if ($validated['is_active']) {
                Resident::query()
                    ->where('apartment_id', $validated['apartment_id'])
                    ->whereKeyNot($resident->id)
                    ->update(['is_active' => false]);
            }

            $resident->update($validated);

            if ($validated['is_active']) {
                $resident->apartment()->update(['status' => 'occupied']);
            }
        });

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_resident',
            'table_name' => 'residents',
            'record_id' => $resident->id,
            'ip_address' => $request->ip(),
            'description' => "{$resident->full_name} sakin kaydı güncellendi.",
        ]);

        return redirect()->route('residents.index')->with('status', 'Sakin güncellendi.');
    }

    public function destroy(Request $request, Resident $resident): RedirectResponse
    {
        $name = $resident->full_name;
        $resident->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_resident',
            'table_name' => 'residents',
            'ip_address' => $request->ip(),
            'description' => "{$name} sakin kaydı silindi.",
        ]);

        return redirect()->route('residents.index')->with('status', 'Sakin silindi.');
    }
}
