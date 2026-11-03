<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

Route::get('/db-test', function () {
    $default = config('database.default');
    $cfg = config("database.connections.{$default}");
    $pass = (string) config("database.connections.{$default}.password");
    $pass_check = [
        'length' => strlen($pass),
        'starts_with' => substr($pass, 0, 3),
        'ends_with' => substr($pass, -3),
        'matches_local' => ($pass === 'eshaq777476080eshaq'),
    ];
    if (isset($cfg['password'])) $cfg['password'] = '***';
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'connected',
            'connection' => $default,
            'pass_check' => $pass_check,
            'config' => $cfg,
            'users_count' => \App\Models\User::count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'connection' => $default,
            'pass_check' => $pass_check,
            'config' => $cfg,
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/media/{path}', function (string $path) {
    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }

    abort(404);
})->where('path', '.*')->name('media.file');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $user = $request->user();

        if (!$user instanceof User || !$user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'هذا الحساب ليس حساب مدير.',
            ])->onlyInput('email');
        }

        return redirect()->intended('/admin/dashboard');
    }

    return back()->withErrors([
        'email' => 'البريد أو كلمة المرور غير صحيحة.',
    ])->onlyInput('email');
})->name('login.submit');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

require __DIR__ . '/admin.php';
