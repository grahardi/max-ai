@extends('layouts.app')

@section('title', 'SHA256 - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-fuchsia-500 to-violet-500 flex items-center justify-center text-3xl shadow mb-3">#️⃣</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">SHA256</h1>
        <p class="mt-2 text-slate-500">Buat hash SHA-256 dari teks apapun.</p>
    </div>

    <div class="rounded-2xl border border-violet-100 bg-white p-6">
        <form action="{{ route('tools.encrypt.sha256.process') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Teks</label>
                <textarea name="text" rows="5" required
                          class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-violet-400 focus:ring-violet-400">{{ old('text') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-fuchsia-500 to-violet-600 hover:opacity-90 text-white font-semibold py-3 transition">
                Buat Hash SHA256
            </button>
        </form>

        @if (session('result'))
            <div class="mt-6">
                <p class="text-sm font-medium text-slate-500 mb-1">Hasil Hash</p>
                <div class="rounded-xl bg-violet-50 border border-violet-200 p-3 text-sm font-mono break-all">{{ session('result') }}</div>
            </div>
        @endif

        <p class="mt-6 text-xs text-slate-400">
            ⚠️ SHA-256 tanpa salt bukan pilihan aman untuk hashing password. Untuk password, gunakan tool <a href="{{ route('tools.encrypt.bcrypt') }}" class="text-violet-600 underline">Bcrypt</a>.
        </p>
    </div>

</section>
@endsection
