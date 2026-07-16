@extends('layouts.app', ['title' => 'Duyuru Gönder - AidatPro'])

@php
    $phoneResidents = $residents->filter(fn ($resident) => filled($resident->phone))->values();
    $mailResidents = $residents->filter(fn ($resident) => filled($resident->email))->values();
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Duyuru gönderimi</div>
                    <h2 class="page-title">{{ $announcement->title }}</h2>
                    <div class="text-secondary mt-1">WhatsApp için manuel aktarım, mail için sırayla gönderim ekranı.</div>
                </div>
                <div class="col-auto">
                    <a class="btn" href="{{ route('announcements.index') }}">
                        <x-icon name="arrow-left" class="me-1" /> Duyurulara Dön
                    </a>
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

            <div class="card mb-3">
                <div class="card-body">
                    <form class="row g-2 align-items-end" method="get" action="{{ route('announcements.send', $announcement) }}">
                        <div class="col-md-4">
                            <label class="form-label">Site</label>
                            <input class="form-control" value="{{ $site->name }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blok</label>
                            <select class="form-select" name="block_id">
                                <option value="">Tüm bloklar</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" @selected($blockId === $block->id)>{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn" type="submit"><x-icon name="filter" class="me-1" /> Alıcıları Filtrele</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#tab-whatsapp" data-bs-toggle="tab">
                                        <x-icon name="brand-whatsapp" class="me-1" /> WhatsApp ile Gönder
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#tab-mail" data-bs-toggle="tab">
                                        <x-icon name="mail" class="me-1" /> Mail ile Gönder
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane active show" id="tab-whatsapp">
                                    <div class="row g-4">
                                        <div class="col-md-7">
                                            <label class="form-label">WhatsApp Mesajı</label>
                                            <textarea class="form-control" id="whatsappMessage" rows="7">{{ old('whatsapp_message', $defaultMessage) }}</textarea>

                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                <button class="btn" type="button" data-copy-target="#whatsappMessage">
                                                    <x-icon name="copy" class="me-1" /> Mesajı Kopyala
                                                </button>
                                                <button class="btn" type="button" data-copy-target="#whatsappPhones">
                                                    <x-icon name="copy" class="me-1" /> Numaraları Kopyala
                                                </button>
                                            </div>

                                            <label class="form-label mt-4">Seçili Telefonlar</label>
                                            <textarea class="form-control" id="whatsappPhones" rows="4" readonly></textarea>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="api-panel">
                                                <div class="avatar bg-green-lt mb-3"><x-icon name="brand-whatsapp" /></div>
                                                <h3>WhatsApp Gönderim Modu</h3>
                                                <p class="text-secondary mb-3">Bu ekranda mesaj ve numaralar hazırlanır. Sistem içinden otomatik toplu WhatsApp gönderimi için WhatsApp Business Cloud API bağlantısı gerekir.</p>
                                                <div class="d-grid gap-2">
                                                    <a class="btn btn-success" id="whatsappOpenLink" target="_blank" rel="noopener">
                                                        <x-icon name="brand-whatsapp" class="me-1" /> WhatsApp Web'de Aç
                                                    </a>
                                                    <button class="btn" type="button" data-copy-target="#whatsappPhones">
                                                        <x-icon name="copy" class="me-1" /> Toplu Numara Kopyala
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning mt-3 mb-0">
                                                WhatsApp Web oturumunu sisteme bağlayıp aynı ekrandan otomatik toplu mesaj göndermek resmi web entegrasyonu değildir. Bu iş güvenli şekilde API anahtarı ve onaylı WhatsApp Business hesabı ile yapılır.
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="fw-bold">WhatsApp Alıcıları</div>
                                            <div class="text-secondary small">{{ $phoneResidents->count() }} telefon numarası bulundu.</div>
                                        </div>
                                        <button class="btn btn-sm" type="button" data-toggle-checks="[data-whatsapp-recipient]">Tümünü Seç</button>
                                    </div>

                                    <div class="recipient-list">
                                        @forelse ($phoneResidents as $resident)
                                            <label class="recipient-row">
                                                <input class="form-check-input" type="checkbox" data-whatsapp-recipient value="{{ $resident->phone }}" checked>
                                                <span class="recipient-main">
                                                    <span class="fw-semibold">{{ $resident->full_name }}</span>
                                                    <span class="text-secondary small">{{ $resident->apartment->buildingBlock->name }} / {{ $resident->apartment->number }}</span>
                                                </span>
                                                <span class="recipient-contact">{{ $resident->phone }}</span>
                                            </label>
                                        @empty
                                            <div class="empty">
                                                <div class="empty-icon"><x-icon name="phone" /></div>
                                                <p class="empty-title">Telefon numarası yok</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab-mail">
                                    <form method="post" action="{{ route('announcements.mail', $announcement) }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Konu</label>
                                                <input class="form-control" name="subject" value="{{ old('subject', $announcement->title) }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Mail Mesajı</label>
                                                <textarea class="form-control" name="message" rows="7" required>{{ old('message', $defaultMessage) }}</textarea>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <div class="fw-bold">Mail Alıcıları</div>
                                                <div class="text-secondary small">{{ $mailResidents->count() }} mail adresi bulundu. Gönderim tek tek yapılır.</div>
                                            </div>
                                            <button class="btn btn-sm" type="button" data-toggle-checks="[data-mail-recipient]">Tümünü Seç</button>
                                        </div>

                                        <div class="recipient-list">
                                            @forelse ($mailResidents as $resident)
                                                <label class="recipient-row">
                                                    <input class="form-check-input" type="checkbox" name="resident_ids[]" data-mail-recipient value="{{ $resident->id }}" checked>
                                                    <span class="recipient-main">
                                                        <span class="fw-semibold">{{ $resident->full_name }}</span>
                                                        <span class="text-secondary small">{{ $resident->apartment->buildingBlock->name }} / {{ $resident->apartment->number }}</span>
                                                    </span>
                                                    <span class="recipient-contact">{{ $resident->email }}</span>
                                                </label>
                                            @empty
                                                <div class="empty">
                                                    <div class="empty-icon"><x-icon name="mail" /></div>
                                                    <p class="empty-title">Mail adresi yok</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="text-end mt-4">
                                            <button class="btn btn-primary" type="submit" @disabled($mailResidents->isEmpty())>
                                                <x-icon name="send" class="me-1" /> Seçilenlere Sırayla Gönder
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Duyuru Önizleme</h3></div>
                        <div class="card-body">
                            <div class="badge bg-primary-lt mb-3">{{ $announcement->publish_date->format('d.m.Y') }}</div>
                            <h3>{{ $announcement->title }}</h3>
                            <p class="text-secondary mb-0">{{ $announcement->content }}</p>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header"><h3 class="card-title">Alıcı Özeti</h3></div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span><x-icon name="brand-whatsapp" class="me-2" />WhatsApp</span>
                                <strong>{{ $phoneResidents->count() }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span><x-icon name="mail" class="me-2" />Mail</span>
                                <strong>{{ $mailResidents->count() }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span><x-icon name="users" class="me-2" />Aktif sakin</span>
                                <strong>{{ $residents->count() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
