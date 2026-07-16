<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($credentials['email']))->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors(['email' => 'E-posta veya şifre hatalı.'])->onlyInput('email');
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            return back()->withErrors(['email' => 'Hesap geçici olarak kilitli. Biraz sonra tekrar deneyin.'])->onlyInput('email');
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            $attempts = $user->failed_attempts + 1;

            $user->forceFill([
                'failed_attempts' => $attempts,
                'locked_until' => $attempts >= 5 ? now()->addMinutes(15) : null,
            ])->save();

            return back()->withErrors(['email' => 'E-posta veya şifre hatalı.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $user->id,
            'ip_address' => $request->ip(),
            'description' => 'Yönetici girişi yapıldı.',
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'logout',
                'table_name' => 'users',
                'record_id' => $request->user()->id,
                'ip_address' => $request->ip(),
                'description' => 'Yönetici çıkışı yapıldı.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
