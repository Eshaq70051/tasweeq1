<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

Route::get('/db-test', function (Request $request) {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'connected',
            'database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
            'users_count' => \App\Models\User::count(),
            'products_count' => \App\Models\Product::count(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'path' => $request->path(),
            'url' => $request->url(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
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
