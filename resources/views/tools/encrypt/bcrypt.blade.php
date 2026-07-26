@extends('layouts.app')

@section('title', 'Bcrypt - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-3xl shadow mb-3">🔑</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Bcrypt</h1>
        <p class="mt-2 text-slate-500">Hash teks pakai Bcrypt, atau verifikasi teks terhadap hash yang sudah ada.</p>
    </div>

    <div class="rounded-2xl border border-violet-100 bg-white p-6">

        <div class="flex gap-2 mb-5 text-sm font-semibold">
            <a href="?tab=hash" class="px-3 py-1.5 rounded-lg {{ old('action', session('action', 'hash')) === 'hash' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-600' }}">Buat Hash</a>
            <a href="?tab=verify" class="px-3 py-1.5 rounded-lg {{ old('action', session('action')) === 'verify' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-600' }}">Verifikasi Hash</a>
        </div>

        <form action="{{ route('tools.encrypt.bcrypt.process') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="{{ request('tab', old('action', 'hash')) === 'verify' ? 'verify' : 'hash' }}">

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">
                    {{ request('tab', old('action')) === 'verify' ? 'Teks (plain text)' : 'Teks yang mau di-hash' }}
                </label>
                <textarea name="text" rows="3" required
                          class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-violet-400 focus:ring-violet-400">{{ old('text') }}</textarea>
            </div>

            @if (request('tab', old('action')) === 'verify')
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Hash Bcrypt untuk dibandingkan</label>
                    <textarea name="hash" rows="2" required
                              class="w-full rounded-xl border border-slate-200 p-3 text-sm font-mono focus:border-violet-400 focus:ring-violet-400">{{ old('hash') }}</textarea>
                </div>
            @endif

            <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 hover:opacity-90 text-white font-semibold py-3 transition">
                {{ request('tab', old('action')) === 'verify' ? 'Verifikasi' : 'Buat Hash' }}
            </button>
        </form>

        @if (session('result'))
            <div class="mt-6">
                <p class="text-sm font-medium text-slate-500 mb-1">Hasil Hash</p>
                <div class="rounded-xl bg-violet-50 border border-violet-200 p-3 text-sm font-mono break-all">{{ session('result') }}</div>
            </div>
        @endif

        @if (session()->has('verify_result'))
            <div class="mt-6">
                @if (session('verify_result'))
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700 font-medium">✅ Cocok! Teks sesuai dengan hash.</div>
                @else
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700 font-medium">❌ Tidak cocok. Teks tidak sesuai dengan hash.</div>
                @endif
            </div>
        @endif

    </div>

</section>
@endsection
