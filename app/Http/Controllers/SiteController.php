<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        return view('sites.index', [
            'sites' => Site::query()
                ->withCount(['buildingBlocks', 'expenses', 'announcements'])
                ->with(['buildingBlocks' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:sites,name'],
            'address' => ['nullable', 'string', 'max:255'],
            'blocks' => ['nullable', 'array'],
            'blocks.*' => ['nullable', 'string', 'max:80'],
        ]);

        $site = Site::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        $this->syncBlocks($site, $validated['blocks'] ?? []);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_site',
            'table_name' => 'sites',
            'record_id' => $site->id,
            'ip_address' => $request->ip(),
            'description' => "{$site->name} sitesi oluşturuldu.",
        ]);

        return redirect()->route('sites.index')->with('status', 'Site oluşturuldu.');
    }

    public function edit(Site $site): View
    {
        return view('sites.edit', [
            'site' => $site->load(['buildingBlocks' => fn ($query) => $query->orderBy('name')]),
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160', Rule::unique('sites', 'name')->ignore($site->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'blocks' => ['nullable', 'array'],
            'blocks.*' => ['nullable', 'string', 'max:80'],
        ]);

        $site->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        $this->syncBlocks($site, $validated['blocks'] ?? []);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_site',
            'table_name' => 'sites',
            'record_id' => $site->id,
            'ip_address' => $request->ip(),
            'description' => "{$site->name} sitesi güncellendi.",
        ]);

        return redirect()->route('sites.index')->with('status', 'Site güncellendi.');
    }

    public function destroy(Request $request, Site $site): RedirectResponse
    {
        if ($site->buildingBlocks()->whereHas('apartments')->exists()) {
            return back()->withErrors(['delete' => 'Bu siteye bağlı daireler var. Önce daireleri taşımalı veya silmelisiniz.']);
        }

        $name = $site->name;
        $site->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_site',
            'table_name' => 'sites',
            'ip_address' => $request->ip(),
            'description' => "{$name} sitesi silindi.",
        ]);

        return redirect()->route('sites.index')->with('status', 'Site silindi.');
    }

    private function syncBlocks(Site $site, array $blocks): void
    {
        collect($blocks)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->each(fn (string $name) => BuildingBlock::firstOrCreate([
                'site_id' => $site->id,
                'name' => $name,
            ]));
    }
}
