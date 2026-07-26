<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MemberFolder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan underscore (tanpa spasi).',
            'username.unique' => 'Username ini sudah dipakai.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'is_approved' => false,
        ]);

        MemberFolder::create([
            'user_id' => $user->id,
            'parent_id' => null,
            'name' => 'Hasil',
            'is_system' => true,
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Akun kamu menunggu persetujuan admin sebelum bisa login.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->input('login');
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $loginInput)->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Email/username atau password salah.');
        }

        if (! $user->is_approved) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Akun kamu masih menunggu persetujuan admin. Silakan coba lagi nanti.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirect = $user->isAdmin() ? route('admin.users.index') : route('member.dashboard');

        return redirect()->intended($redirect)->with('success', 'Berhasil login!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Berhasil logout.');
    }
}
