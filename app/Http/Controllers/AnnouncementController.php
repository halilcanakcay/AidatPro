<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BuildingBlock;
use App\Models\Resident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()->orderBy('name')->get();
        $site = Site::query()->find($request->query('site_id')) ?? $sites->firstOrFail();
        $year = (int) $request->query('year', 2026);
        $month = $request->filled('month') ? max(1, min(12, (int) $request->query('month'))) : null;
        $search = trim((string) $request->query('search', ''));

        $announcements = Announcement::query()
            ->where('site_id', $site->id)
            ->whereYear('publish_date', $year)
            ->when($month, fn ($query) => $query->whereMonth('publish_date', $month))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest('publish_date')
            ->paginate(12)
            ->withQueryString();

        return view('announcements.index', [
            'site' => $site,
            'sites' => $sites,
            'announcements' => $announcements,
            'months' => $this->months(),
            'year' => $year,
            'month' => $month,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('announcements.create', [
            'site' => Site::query()->firstOrFail(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:2000'],
            'publish_date' => ['required', 'date'],
        ]);

        $site = Site::query()->firstOrFail();
        $announcement = Announcement::create($validated + ['site_id' => $site->id]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_announcement',
            'table_name' => 'announcements',
            'record_id' => $announcement->id,
            'ip_address' => $request->ip(),
            'description' => "{$announcement->title} duyurusu yayımlandı.",
        ]);

        return redirect()->route('announcements.index')->with('status', 'Duyuru yayımlandı.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:2000'],
            'publish_date' => ['required', 'date'],
        ]);

        $announcement->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_announcement',
            'table_name' => 'announcements',
            'record_id' => $announcement->id,
            'ip_address' => $request->ip(),
            'description' => "{$announcement->title} duyurusu güncellendi.",
        ]);

        return redirect()->route('announcements.index')->with('status', 'Duyuru güncellendi.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_announcement',
            'table_name' => 'announcements',
            'ip_address' => $request->ip(),
            'description' => "{$title} duyurusu silindi.",
        ]);

        return redirect()->route('announcements.index')->with('status', 'Duyuru silindi.');
    }

    public function send(Request $request, Announcement $announcement): View
    {
        $site = $announcement->site()->firstOrFail();
        $blocks = BuildingBlock::query()->where('site_id', $site->id)->orderBy('name')->get();
        $blockId = $blocks->firstWhere('id', $request->integer('block_id'))?->id;
        $residents = Resident::query()
            ->with(['apartment.buildingBlock'])
            ->where('is_active', true)
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->when($blockId, fn ($query) => $query->whereHas('apartment', fn ($apartment) => $apartment->where('building_block_id', $blockId)))
            ->join('apartments', 'apartments.id', '=', 'residents.apartment_id')
            ->join('building_blocks', 'building_blocks.id', '=', 'apartments.building_block_id')
            ->orderBy('building_blocks.name')
            ->orderBy('apartments.number')
            ->select('residents.*')
            ->get();

        return view('announcements.send', [
            'site' => $site,
            'blocks' => $blocks,
            'blockId' => $blockId,
            'announcement' => $announcement,
            'residents' => $residents,
            'defaultMessage' => trim($announcement->title . "\n\n" . $announcement->content),
        ]);
    }

    public function sendMail(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:4000'],
            'resident_ids' => ['required', 'array', 'min:1'],
            'resident_ids.*' => ['integer', 'exists:residents,id'],
        ]);

        $site = Site::query()->firstOrFail();
        $recipients = Resident::query()
            ->whereIn('id', $validated['resident_ids'])
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->whereHas('apartment.buildingBlock', fn ($query) => $query->where('site_id', $site->id))
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $resident) {
            try {
                Mail::raw($validated['message'], function ($mail) use ($resident, $validated) {
                    $mail->to($resident->email, $resident->full_name)
                        ->subject($validated['subject']);
                });

                $sent++;
            } catch (Throwable) {
                $failed++;
            }
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'send_announcement_mail',
            'table_name' => 'announcements',
            'record_id' => $announcement->id,
            'ip_address' => $request->ip(),
            'description' => "{$announcement->title} duyurusu için {$sent} mail gönderildi, {$failed} hata oluştu.",
        ]);

        return back()->with('status', "{$sent} mail sırayla gönderildi. {$failed} gönderim başarısız.");
    }

    private function months(): array
    {
        return [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık',
        ];
    }
}
