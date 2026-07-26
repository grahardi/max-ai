@extends('layouts.app')

@section('title', 'Login - Max AI')

@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 py-14">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-600 to-fuchsia-600 flex items-center justify-center text-3xl shadow mb-3">👤</div>
        <h1 class="text-2xl font-extrabold text-slate-900">Masuk ke Member Area</h1>
        <p class="mt-2 text-sm text-slate-500">Upload, simpan, dan proses file kamu kapan saja.</p>
    </div>

    <form action="{{ route('login') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email atau Username</label>
            <input type="text" name="login" value="{{ old('login') }}" required autofocus
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('login')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('password')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-500">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            Ingat saya
        </label>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 hover:opacity-90 text-white font-semibold py-3 transition">
            Masuk
        </button>
    </form>

    <p class="text-center text-sm text-slate-500 mt-5">
        Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 font-medium">Daftar sekarang</a>
    </p>

</section>
@endsection
