@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'active' => ['dashboard']],
        ['label' => 'Site Yönetimi', 'route' => 'sites.index', 'icon' => 'building-community', 'active' => ['sites.*']],
        ['label' => 'Daireler', 'route' => 'apartments.index', 'icon' => 'building', 'active' => ['apartments.*']],
        ['label' => 'Sakinler', 'route' => 'residents.index', 'icon' => 'users', 'active' => ['residents.*']],
        ['label' => 'Aidatlar', 'route' => 'dues.index', 'icon' => 'receipt', 'active' => ['dues.*']],
        ['label' => 'Ödemeler', 'route' => 'payments.index', 'icon' => 'wallet', 'active' => ['payments.*']],
        ['label' => 'Giderler', 'route' => 'expenses.index', 'icon' => 'report-money', 'active' => ['expenses.*']],
        ['label' => 'Duyurular', 'route' => 'announcements.index', 'icon' => 'speakerphone', 'active' => ['announcements.*']],
        ['label' => 'Yetkililer', 'route' => 'authorities.index', 'icon' => 'shield-lock', 'active' => ['authorities.*']],
        ['label' => 'Raporlar', 'route' => 'reports.index', 'icon' => 'report-analytics', 'active' => ['reports.*']],
        ['label' => 'Ayarlar', 'route' => 'settings.index', 'icon' => 'settings', 'active' => ['settings.*']],
    ];

    $quickActions = [
        ['label' => 'Site Ekle', 'route' => 'sites.create', 'icon' => 'building-community'],
        ['label' => 'Daire Ekle', 'route' => 'apartments.create', 'icon' => 'building-plus'],
        ['label' => 'Sakin Ekle', 'route' => 'residents.create', 'icon' => 'user-plus'],
        ['label' => 'Tahakkuk Oluştur', 'route' => 'dues.create', 'icon' => 'receipt'],
        ['label' => 'Ödeme Al', 'route' => 'payments.create', 'icon' => 'wallet'],
        ['label' => 'Gider Ekle', 'route' => 'expenses.create', 'icon' => 'report-money'],
        ['label' => 'Duyuru Ekle', 'route' => 'announcements.create', 'icon' => 'speakerphone'],
        ['label' => 'Yetkili Ekle', 'route' => 'authorities.create', 'icon' => 'user-cog'],
    ];

    $topbarAnnouncements = \App\Models\Announcement::query()
        ->whereDate('publish_date', '<=', now())
        ->latest('publish_date')
        ->limit(6)
        ->get();
    $announcementCount = $topbarAnnouncements->count();
@endphp

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AidatPro' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="app-sidebar d-print-none" id="appSidebar">
            <a class="sidebar-brand" href="{{ route('dashboard') }}">
                <span class="brand-mark"><x-icon name="building-skyscraper" /></span>
                <span>
                    <span class="fw-bold">AidatPro</span>
                    <span class="d-block text-secondary small fw-normal">Yönetim paneli</span>
                </span>
            </a>

            <nav class="sidebar-nav">
                @foreach ($navItems as $item)
                    @php $isActive = request()->routeIs(...$item['active']); @endphp
                    <a class="sidebar-link {{ $isActive ? 'active' : '' }}" href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif>
                        <span class="sidebar-icon"><x-icon :name="$item['icon']" /></span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div class="sidebar-group">
                    <div class="sidebar-group-title">Entegrasyonlar</div>
                    <a class="sidebar-link sidebar-link-child {{ request()->routeIs('integrations.vakifbank') ? 'active' : '' }}" href="{{ route('integrations.vakifbank') }}" @if (request()->routeIs('integrations.vakifbank')) aria-current="page" @endif>
                        <span class="sidebar-icon"><x-icon name="building-bank" /></span>
                        <span>Vakıfbank</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-support">
                <a class="btn btn-success w-100 justify-content-start" href="https://wa.me/90501828906?text={{ rawurlencode('Yardım almak istiyorum') }}" target="_blank" rel="noopener">
                    <x-icon name="brand-whatsapp" class="me-2" /> Canlı Destek
                </a>
            </div>
        </aside>

        <div class="app-main">
            <header class="app-topbar d-print-none">
                <div class="d-lg-none mobile-topbar-brand">
                    <button class="btn btn-icon" type="button" data-sidebar-toggle aria-label="Menüyü aç" aria-controls="appSidebar" aria-expanded="false">
                        <x-icon name="menu-2" />
                    </button>
                    <a class="mobile-brand-text" href="{{ route('dashboard') }}">AidatPro</a>
                </div>

                @if ($announcementCount > 0)
                    <div class="topbar-announcements d-none d-md-flex {{ $announcementCount === 1 ? 'is-static' : '' }}" data-announcement-ticker data-announcement-count="{{ $announcementCount }}">
                        <span class="topbar-announcement-icon"><x-icon name="speakerphone" /></span>
                        <div class="topbar-announcement-window">
                            <div class="topbar-announcement-track">
                                @foreach ($topbarAnnouncements as $announcement)
                                    <a class="topbar-announcement-item" href="{{ route('announcements.index', ['search' => $announcement->title]) }}">
                                        <span class="fw-semibold">{{ $announcement->title }}</span>
                                        <span class="text-secondary">- {{ \Illuminate\Support\Str::limit($announcement->content, 95) }}</span>
                                    </a>
                                @endforeach

                                @if ($announcementCount > 1)
                                    @php $firstAnnouncement = $topbarAnnouncements->first(); @endphp
                                    <a class="topbar-announcement-item" href="{{ route('announcements.index', ['search' => $firstAnnouncement->title]) }}" aria-hidden="true">
                                        <span class="fw-semibold">{{ $firstAnnouncement->title }}</span>
                                        <span class="text-secondary">- {{ \Illuminate\Support\Str::limit($firstAnnouncement->content, 95) }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="ms-auto d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle quick-create-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <x-icon name="plus" class="quick-create-icon me-1" />
                            <span class="quick-create-label">Yeni Kayıt</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            @foreach ($quickActions as $action)
                                <a class="dropdown-item" href="{{ route($action['route']) }}">
                                    <x-icon :name="$action['icon']" class="me-2" />{{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a class="btn btn-icon btn-ghost-secondary" href="{{ route('announcements.index') }}" aria-label="Duyurular">
                        <x-icon name="bell" />
                    </a>

                    <div class="dropdown">
                        <button class="btn user-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar avatar-sm bg-primary-lt">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="user-menu-text">
                                <span class="fw-semibold">{{ auth()->user()->name }}</span>
                                <span class="text-secondary small">{{ auth()->user()->role }}</span>
                            </span>
                            <x-icon name="chevron-down" class="text-secondary" />
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-header">Oturum</div>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <x-icon name="logout" class="me-2" />Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="page-wrapper">
                @yield('content')
            </main>

            <footer class="app-footer d-print-none">
                <div class="container-xl">
                    <span class="fw-semibold">AidatPro</span>
                    <span class="text-secondary">Geliştirici: Güvenli Yazılım</span>
                </div>
            </footer>
        </div>
    </div>

    <div class="sidebar-backdrop d-print-none" data-sidebar-backdrop></div>

    <div class="app-confirm-backdrop" data-confirm-modal aria-hidden="true">
        <div class="app-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle">
            <div class="app-confirm-icon"><x-icon name="alert-circle" /></div>
            <div class="app-confirm-content">
                <h3 id="appConfirmTitle">İşlemi onayla</h3>
                <p data-confirm-message>Bu işlem devam etsin mi?</p>
            </div>
            <div class="app-confirm-actions">
                <button class="btn" type="button" data-confirm-cancel>Vazgeç</button>
                <button class="btn btn-danger" type="button" data-confirm-accept>Onayla</button>
            </div>
        </div>
    </div>
</body>
</html>
