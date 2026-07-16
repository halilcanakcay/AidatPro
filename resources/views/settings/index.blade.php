@extends('layouts.app', ['title' => 'Ayarlar - AidatPro'])

@php
    $tabs = [
        'telegram' => ['label' => 'Telegram API', 'icon' => 'brand-telegram'],
        'general' => ['label' => 'Genel', 'icon' => 'settings-cog'],
    ];
@endphp

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Sistem</div>
                    <h2 class="page-title">Ayarlar</h2>
                    <div class="text-secondary mt-1">Telegram ve ileride eklenecek genel servis ayarlarını buradan yönetin.</div>
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

            <div class="panel-tabs mb-3" role="tablist" aria-label="Ayar sekmeleri">
                @foreach ($tabs as $key => $item)
                    <a class="panel-tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('settings.index', ['tab' => $key]) }}" @if ($tab === $key) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="card">
                @if ($tab === 'telegram')
                    <form method="post" action="{{ route('settings.telegram') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bot Token</label>
                                    <input class="form-control" type="password" name="bot_token" placeholder="{{ $settings['telegram_bot_token_masked'] ?? '123456:ABC...' }}">
                                    <div class="form-hint">Boş bırakırsanız kayıtlı token korunur. Token şifreli saklanır.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Grup Chat ID</label>
                                    <input class="form-control" name="chat_id" value="{{ old('chat_id', $settings['telegram_chat_id']) }}" placeholder="-1001234567890">
                                </div>
                                <div class="col-12">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings['telegram_enabled']) === '1')>
                                        <span class="form-check-label">Telegram bildirimleri aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit"><x-icon name="device-floppy" class="me-1" /> Telegram Ayarlarını Kaydet</button>
                        </div>
                    </form>
                @else
                    <form method="post" action="{{ route('settings.general') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Genel Erişim Adresi</label>
                                    <input class="form-control" name="public_url" value="{{ old('public_url', $settings['app_public_url']) }}" placeholder="https://panel.siteadi.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destek E-posta</label>
                                    <input class="form-control" type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" placeholder="destek@example.com">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit"><x-icon name="device-floppy" class="me-1" /> Genel Ayarları Kaydet</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
