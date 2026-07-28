<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View { return view('auth.login'); }
    public function showRegister(): View { return view('auth.register'); }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $key = Str::lower($validated['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Bạn đăng nhập sai quá nhiều lần. Hãy thử lại sau '.RateLimiter::availableIn($key).' giây.',
            ]);
        }

        $field = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $validated['login'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'Email/tên đăng nhập hoặc mật khẩu không đúng.']);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages(['login' => 'Tài khoản đã bị khóa.']);
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();
        RateLimiter::clear($key);
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['required', 'in:listener,artist'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => Str::lower($data['username']),
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
            'role' => $data['account_type'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('home')->with('success', 'Tài khoản đã được tạo.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Đã đăng xuất.');
    }
}
