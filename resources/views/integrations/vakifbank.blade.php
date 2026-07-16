@extends('layouts.app', ['title' => 'Vakıfbank - AidatPro'])

@php
    $money = fn ($value) => '₺' . number_format((float) $value, 2, ',', '.');
    $statusBadge = [
        'matched' => 'bg-green-lt',
        'manual_matched' => 'bg-blue-lt',
        'unmatched' => 'bg-yellow-lt',
        'needs_review' => 'bg-orange-lt',
        'failed' => 'bg-red-lt',
    ];
    $statusLabel = [
        'matched' => 'Otomatik işlendi',
        'manual_matched' => 'Manuel işlendi',
        'unmatched' => 'Eşleşme yok',
        'needs_review' => 'İnceleme gerekli',
        'failed' => 'Başarısız',
    ];
    $tabs = [
        'transactions' => ['label' => 'Gelen İşlemler', 'icon' => 'database-dollar'],
        'successful' => ['label' => 'Başarılı', 'icon' => 'checks'],
        'failed' => ['label' => 'Başarısız / Manuel Onay', 'icon' => 'alert-triangle'],
        'settings' => ['label' => 'Banka Ayarları', 'icon' => 'settings-cog'],
        'docs' => ['label' => 'Başvuru Rehberi', 'icon' => 'list-check'],
    ];
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Entegrasyonlar / Vakıfbank</div>
                    <h2 class="page-title">Vakıfbank Online Hesap Hareketleri</h2>
                    <div class="text-secondary mt-1">IBAN hesabına gelen hareketleri izleyin, otomatik veya manuel olarak aidata işleyin.</div>
                </div>
                <div class="col-auto">
                    <form class="d-flex gap-2" method="get" action="{{ route('integrations.vakifbank') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <select class="form-select" name="site_id" onchange="this.form.submit()">
                            @foreach ($sites as $filterSite)
                                <option value="{{ $filterSite->id }}" @selected($filterSite->is($site))>{{ $filterSite->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('status'))
                <div class="alert alert-success"><x-icon name="circle-check" class="me-2" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><x-icon name="alert-circle" class="me-2" />{{ $errors->first() }}</div>
            @endif

            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body"><div class="text-secondary">Toplam Hareket</div><div class="h1 mb-0">{{ $stats['total'] }}</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body"><div class="text-secondary">Başarılı</div><div class="h1 mb-0 text-success">{{ $stats['matched'] }}</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body"><div class="text-secondary">Manuel Bekleyen</div><div class="h1 mb-0 text-warning">{{ $stats['failed'] }}</div></div></div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card"><div class="card-body"><div class="text-secondary">Son Kontrol</div><div class="h3 mb-0">{{ $stats['last_synced_at']?->format('d.m.Y H:i') ?? 'Henüz yok' }}</div></div></div>
                </div>
            </div>

            <div class="panel-tabs mb-3" role="tablist" aria-label="Vakıfbank sekmeleri">
                @foreach ($tabs as $key => $item)
                    <a class="panel-tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('integrations.vakifbank', ['site_id' => $site->id, 'tab' => $key]) }}" @if ($tab === $key) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            @if ($tab === 'transactions')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vakıfbanktan Gelen İşlemler</h3>
                        <div class="card-actions">
                            <form method="post" action="{{ route('integrations.vakifbank.sync') }}">
                                @csrf
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                <button class="btn btn-primary" type="submit">
                                    <x-icon name="refresh" class="me-1" /> Manuel Kontrol Et
                                </button>
                            </form>
                        </div>
                    </div>
                    @include('integrations.vakifbank-transaction-table', ['rows' => $transactions, 'showManualAction' => false])
                </div>
            @elseif ($tab === 'successful')
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Aidata İşlenen Başarılı İşlemler</h3></div>
                    @include('integrations.vakifbank-transaction-table', ['rows' => $successfulTransactions, 'showManualAction' => false])
                </div>
            @elseif ($tab === 'failed')
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Başarısız İşlemler ve Manuel Onay</h3></div>
                    @include('integrations.vakifbank-transaction-table', ['rows' => $failedTransactions, 'showManualAction' => true])
                </div>
            @elseif ($tab === 'settings')
                <form class="card" method="post" action="{{ route('integrations.vakifbank.settings') }}">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <div class="card-header"><h3 class="card-title">Vakıfbank Bağlantı Ayarları</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ortam</label>
                                <select class="form-select" name="environment">
                                    <option value="test" @selected(old('environment', $integration->environment) === 'test')>Test</option>
                                    <option value="production" @selected(old('environment', $integration->environment) === 'production')>Canlı</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Müşteri No</label>
                                <input class="form-control" name="customer_no" value="{{ old('customer_no', $integration->customer_no) }}" placeholder="007283000555" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hesap No</label>
                                <input class="form-control" name="account_no" value="{{ old('account_no', $integration->account_no) }}" placeholder="00158000020757464" maxlength="17" required>
                                <div class="form-hint">Dokümana göre 00158 ile başlayan 17 haneli hesap no.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IBAN</label>
                                <input class="form-control" name="iban" value="{{ old('iban', $integration->iban) }}" placeholder="TR...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kurum Kullanıcı</label>
                                <input class="form-control" name="corporate_username" value="{{ old('corporate_username', $integration->corporate_username) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Şifre</label>
                                <input class="form-control" type="password" name="corporate_password" placeholder="{{ $integration->corporate_password ? 'Kayıtlı şifreyi korumak için boş bırakın' : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kontrol Sıklığı</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" name="sync_interval_minutes" value="{{ old('sync_interval_minutes', $integration->sync_interval_minutes) }}" min="1" max="60" required>
                                    <span class="input-group-text">dakika</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">WSDL Servis Adresi</label>
                                <input class="form-control" name="service_url" value="{{ old('service_url', $integration->service_url) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ödeme Açıklama Şablonu</label>
                                <input class="form-control" name="description_template" value="{{ old('description_template', $integration->options['description_template'] ?? 'aidat {daire} {ay} {ad_soyad}') }}" placeholder="aidat {daire} {ay} {ad_soyad}">
                                <div class="form-hint">Örn: aidat A-01 Temmuz Ayşe Aksoy. Sistem kelime sırasına değil, daire kodu + dönem + isim/tutar sinyallerine bakar.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $integration->is_active))>
                                    <span class="form-check-label">Bu site için otomatik banka kontrolü aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary" type="submit"><x-icon name="device-floppy" class="me-1" /> Ayarları Kaydet</button>
                    </div>
                </form>

                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Cronjob ve Otomatik Kontrol</h3></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-6">
                                <div class="api-panel h-100">
                                    <div class="d-flex gap-3">
                                        <span class="avatar bg-blue-lt integration-doc-icon"><x-icon name="clock-play" /></span>
                                        <div class="min-w-0">
                                            <h4 class="mb-1">Otomatik kontrol nasıl başlar?</h4>
                                            <p class="text-secondary mb-0">Sunucuda tek bir cron satırı çalıştırılır. Laravel scheduler her dakika devreye girer, Vakıfbank entegrasyonu ise buradaki kontrol sıklığına göre çalışır.</p>
                                        </div>
                                    </div>
                                    <label class="form-label mt-3">Sunucu cron satırı</label>
                                    <div class="input-group">
                                        <input class="form-control font-monospace" id="vakifbankCronCommand" value="* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1" readonly>
                                        <button class="btn" type="button" data-copy-target="#vakifbankCronCommand"><x-icon name="copy" /></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="api-panel h-100">
                                    <div class="d-flex gap-3">
                                        <span class="avatar bg-green-lt integration-doc-icon"><x-icon name="terminal-2" /></span>
                                        <div class="min-w-0">
                                            <h4 class="mb-1">Manuel terminal kontrolü</h4>
                                            <p class="text-secondary mb-0">Cron beklemeden sunucudan tek seferlik kontrol çalıştırmak için aşağıdaki komut kullanılabilir.</p>
                                        </div>
                                    </div>
                                    <label class="form-label mt-3">Tek seferlik komut</label>
                                    <div class="input-group">
                                        <input class="form-control font-monospace" id="vakifbankManualCommand" value="php artisan bank:sync-vakifbank --site_id={{ $site->id }}" readonly>
                                        <button class="btn" type="button" data-copy-target="#vakifbankManualCommand"><x-icon name="copy" /></button>
                                    </div>
                                    <div class="text-secondary small mt-2">Paneldeki “Manuel Kontrol Et” butonu da aynı kontrol altyapısını tetikler.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Açıklama Kontrol Aracı</h3></div>
                    <div class="card-body">
                        <form class="row g-2 align-items-end" method="get" action="{{ route('integrations.vakifbank') }}">
                            <input type="hidden" name="site_id" value="{{ $site->id }}">
                            <input type="hidden" name="tab" value="settings">
                            <div class="col-lg-7">
                                <label class="form-label">Kullanıcının banka açıklamasına yazdığı metin</label>
                                <input class="form-control" name="preview_description" value="{{ request('preview_description', 'A-01 Temmuz Ayşe Aksoy') }}" placeholder="A-01 Temmuz Ayşe Aksoy">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Tutar</label>
                                <div class="input-group">
                                    <span class="input-group-text">₺</span>
                                    <input class="form-control" type="number" name="preview_amount" step="0.01" value="{{ request('preview_amount', '2450') }}">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <button class="btn btn-primary w-100" type="submit"><x-icon name="checks" class="me-1" /> Kontrol Et</button>
                            </div>
                        </form>

                        @if ($descriptionPreview)
                            <div class="alert mt-3 mb-0 {{ $descriptionPreview['status'] === 'success' ? 'alert-success' : ($descriptionPreview['status'] === 'warning' ? 'alert-warning' : 'alert-danger') }}">
                                <x-icon :name="$descriptionPreview['status'] === 'success' ? 'checks' : 'alert-triangle'" class="me-2" />
                                {{ $descriptionPreview['message'] }}
                                @if (count($descriptionPreview['matches']) > 0)
                                    <div class="mt-2">
                                        @foreach ($descriptionPreview['matches'] as $match)
                                            @php $due = $match['due']; @endphp
                                            <span class="badge bg-blue-lt me-1">
                                                {{ $due->apartment->buildingBlock->name }} / {{ $due->apartment->number }}
                                                · {{ $due->period_month }}/{{ $due->period_year }}
                                                · {{ $due->apartment->activeResident?->full_name ?? 'Sakin yok' }}
                                                · skor {{ $match['score'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'docs')
                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Vakıfbank Başvuru Özeti</h3>
                            </div>
                            <div class="card-body">
                                <div class="integration-info-item mb-4">
                                    <span class="avatar bg-primary-lt integration-doc-icon"><x-icon name="building-bank" /></span>
                                    <div>
                                        <h4 class="mb-1">Bankadan istenecek hizmet</h4>
                                        <p class="text-secondary mb-0">Vakıfbank şubenizden veya müşteri temsilcinizden <strong>Online Hesap Hareketleri Web Servisi</strong> tanımlanmasını isteyin. Bu servis hesaba gelen hareketleri okumak içindir.</p>
                                    </div>
                                </div>

                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto"><span class="badge bg-blue-lt">1</span></div>
                                            <div class="col">
                                                <div class="fw-semibold">Bankaya IBAN ve sunucu IP adresini verin.</div>
                                                <div class="text-secondary small">Aidat hesabı IBAN'ı ve yazılımın çalışacağı sabit dış IP yeterlidir.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto"><span class="badge bg-blue-lt">2</span></div>
                                            <div class="col">
                                                <div class="fw-semibold">Bankadan bağlantı bilgilerini alın.</div>
                                                <div class="text-secondary small">Müşteri no, 17 haneli hesap no, kurum kullanıcı adı, şifre ve servis adresi gerekir.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto"><span class="badge bg-blue-lt">3</span></div>
                                            <div class="col">
                                                <div class="fw-semibold">Bilgileri Banka Ayarları sekmesine girin.</div>
                                                <div class="text-secondary small">Önce test ortamında deneyin, test başarılı olunca canlı ortamı açtırın.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto"><span class="badge bg-green-lt">4</span></div>
                                            <div class="col">
                                                <div class="fw-semibold">Cronjob'u başlatın.</div>
                                                <div class="text-secondary small">Cron komutu Banka Ayarları sekmesinde hazır olarak verilir. Kopyalayıp hosting paneline veya sunucu crontab'ına ekleyin.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-4 mb-0">
                                    <x-icon name="info-circle" class="me-2" />
                                    Sistem eşleşme netse ödemeyi otomatik işler; açıklama veya kişi bilgisi net değilse işlem manuel onaya düşer.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header"><h3 class="card-title">Bankaya Söylenecek Kısa Metin</h3></div>
                            <div class="card-body">
                                <p class="text-secondary mb-0">Aidat hesabımız için Online Hesap Hareketleri Web Servisi açtırmak istiyoruz. Hesabımıza gelen hareketleri yazılımımız okuyacak. Test ve canlı ortam servis bilgilerini, kullanıcı adı ve şifreyi rica ederiz.</p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Sakinlere Açıklama Örneği</h3></div>
                            <div class="card-body">
                                <label class="form-label">Ödeme açıklaması</label>
                                <div class="input-group">
                                    <span class="input-group-text"><x-icon name="message-2" /></span>
                                    <input class="form-control" value="aidat A-01 Temmuz Ayşe Aksoy" readonly>
                                </div>
                                <div class="text-secondary small mt-2">Sadece “aidat” yazılırsa sistem kesin eşleştirme yapamayabilir.</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
