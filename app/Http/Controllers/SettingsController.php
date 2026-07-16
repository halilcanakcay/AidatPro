<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'tab' => $request->query('tab', 'telegram'),
            'settings' => [
                'telegram_bot_token_masked' => SystemSetting::getValue('telegram', 'bot_token') ? '••••••••••••••••' : null,
                'telegram_chat_id' => SystemSetting::getValue('telegram', 'chat_id'),
                'telegram_enabled' => SystemSetting::getValue('telegram', 'enabled', '0'),
                'app_public_url' => SystemSetting::getValue('general', 'public_url', config('app.url')),
                'support_email' => SystemSetting::getValue('general', 'support_email'),
            ],
        ]);
    }

    public function updateTelegram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bot_token' => ['nullable', 'string', 'max:160'],
            'chat_id' => ['nullable', 'string', 'max:80'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['bot_token'])) {
            SystemSetting::setValue('telegram', 'bot_token', $validated['bot_token'], true);
        }

        SystemSetting::setValue('telegram', 'chat_id', $validated['chat_id'] ?? null);
        SystemSetting::setValue('telegram', 'enabled', $request->boolean('enabled') ? '1' : '0');

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_telegram_settings',
            'table_name' => 'system_settings',
            'ip_address' => $request->ip(),
            'description' => 'Telegram API ayarları güncellendi.',
        ]);

        return redirect()->route('settings.index', ['tab' => 'telegram'])->with('status', 'Telegram ayarları kaydedildi.');
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'public_url' => ['nullable', 'url', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:160'],
        ]);

        SystemSetting::setValue('general', 'public_url', $validated['public_url'] ?? null);
        SystemSetting::setValue('general', 'support_email', $validated['support_email'] ?? null);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_general_settings',
            'table_name' => 'system_settings',
            'ip_address' => $request->ip(),
            'description' => 'Genel sistem ayarları güncellendi.',
        ]);

        return redirect()->route('settings.index', ['tab' => 'general'])->with('status', 'Genel ayarlar kaydedildi.');
    }
}
