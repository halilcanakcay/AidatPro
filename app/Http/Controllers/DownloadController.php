<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function show(): View
    {
        return view('downloads.index');
    }

    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'max:100'],
        ]);

        $passwordHash = (string) config('downloads.password_hash');

        if ($passwordHash === '' || ! Hash::check($validated['password'], $passwordHash)) {
            return back()
                ->withErrors(['password' => 'İndirme şifresi hatalı.'])
                ->onlyInput();
        }

        $archivePath = (string) config('downloads.archive_path');
        abort_unless(is_file($archivePath), 404, 'İndirme paketi bulunamadı.');

        return response()->download(
            $archivePath,
            (string) config('downloads.filename'),
            ['Content-Type' => 'application/zip'],
        );
    }
}
