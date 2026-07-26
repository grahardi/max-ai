@extends('layouts.app')

@section('title', 'Daftar Akun - Max AI')

@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 py-14">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-600 to-fuchsia-600 flex items-center justify-center text-3xl shadow mb-3">✨</div>
        <h1 class="text-2xl font-extrabold text-slate-900">Buat Akun Member</h1>
        <p class="mt-2 text-sm text-slate-500">Gratis — simpan & proses file kamu kapan saja.</p>
    </div>

    <form action="{{ route('register') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('name')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            <p class="text-xs text-slate-400 mt-1">Huruf, angka, strip, underscore saja (tanpa spasi).</p>
            @error('username')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('email')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter.</p>
            @error('password')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 hover:opacity-90 text-white font-semibold py-3 transition">
            Daftar
        </button>
    </form>

    <p class="text-center text-sm text-slate-500 mt-5">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-medium">Masuk di sini</a>
    </p>

</section>
@endsection
