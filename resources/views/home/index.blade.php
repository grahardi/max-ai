@extends('layouts.app')

@section('title', 'Max AI - Kumpulan Tools AI Gratis')

@section('content')

<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-fuchsia-600 to-rose-500 opacity-95"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.25),_transparent_50%)]"></div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-16 pb-14 text-center text-white">
        <span class="inline-block rounded-full bg-white/15 backdrop-blur text-xs font-semibold px-3 py-1 mb-4 border border-white/20">
            ✨ Kumpulan AI Tools dalam satu tempat
        </span>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">
            Tools AI yang bikin kerjaan <span class="underline decoration-white/40 decoration-8 underline-offset-4">lebih cepat selesai</span>
        </h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto">
            Hapus background foto, gabung & pecah PDF, ubah gambar ke PDF, sampai baca teks dari foto (OCR). Gratis, cepat, tanpa install apapun.
        </p>
    </div>
</section>

<section id="tools" class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 pb-20 space-y-14">

    {{-- ===== Proses Gambar ===== --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">🖼️</span>
            <h2 class="text-lg font-bold text-slate-900">Proses Gambar</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <a href="{{ route('tools.remove-background') }}"
               class="group rounded-2xl border border-indigo-100 bg-white p-6 hover:shadow-xl hover:-translate-y-0.5 transition">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 flex items-center justify-center text-2xl mb-4 shadow-sm">✂️</div>
                <h3 class="font-semibold text-lg text-slate-900 group-hover:text-indigo-600">Remove Background</h3>
                <p class="mt-1 text-sm text-slate-500">Hapus background foto otomatis dengan AI, hasil PNG transparan.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-indigo-600">Coba sekarang &rarr;</span>
            </a>

            <a href="{{ route('tools.image-to-pdf') }}"
               class="group rounded-2xl border border-indigo-100 bg-white p-6 hover:shadow-xl hover:-translate-y-0.5 transition">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 flex items-center justify-center text-2xl mb-4 shadow-sm">📎</div>
                <h3 class="font-semibold text-lg text-slate-900 group-hover:text-indigo-600">Gambar ke PDF</h3>
                <p class="mt-1 text-sm text-slate-500">Gabungkan beberapa foto langsung jadi satu file PDF rapi.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-indigo-600">Coba sekarang &rarr;</span>
            </a>

            <a href="{{ route('tools.image-to-text') }}"
               class="group rounded-2xl border border-indigo-100 bg-white p-6 hover:shadow-xl hover:-translate-y-0.5 transition">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-2xl mb-4 shadow-sm">🔤</div>
                <h3 class="font-semibold text-lg text-slate-900 group-hover:text-indigo-600">Gambar ke Teks (OCR)</h3>
                <p class="mt-1 text-sm text-slate-500">Ekstrak teks dari foto atau hasil scan secara otomatis.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-indigo-600">Coba sekarang &rarr;</span>
            </a>
        </div>
    </div>

    {{-- ===== Proses PDF ===== --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="h-8 w-8 rounded-lg bg-rose-100 flex items-center justify-center">📄</span>
            <h2 class="text-lg font-bold text-slate-900">Proses PDF</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">

            <a href="{{ route('tools.merge-pdf') }}"
               class="group rounded-2xl border border-rose-100 bg-white p-6 hover:shadow-xl hover:-translate-y-0.5 transition">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-rose-500 to-orange-400 flex items-center justify-center text-2xl mb-4 shadow-sm">🧩</div>
                <h3 class="font-semibold text-lg text-slate-900 group-hover:text-rose-600">Gabung PDF (Merge)</h3>
                <p class="mt-1 text-sm text-slate-500">Satukan beberapa file PDF jadi satu dokumen, urutan sesuai upload.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-rose-600">Coba sekarang &rarr;</span>
            </a>

            <a href="{{ route('tools.split-pdf') }}"
               class="group rounded-2xl border border-rose-100 bg-white p-6 hover:shadow-xl hover:-translate-y-0.5 transition">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-orange-500 to-amber-400 flex items-center justify-center text-2xl mb-4 shadow-sm">✂️</div>
                <h3 class="font-semibold text-lg text-slate-900 group-hover:text-rose-600">Pecah PDF (Split)</h3>
                <p class="mt-1 text-sm text-slate-500">Pecah PDF jadi file per-halaman, langsung download dalam ZIP.</p>
                <span class="mt-4 inline-flex text-sm font-medium text-rose-600">Coba sekarang &rarr;</span>
            </a>
        </div>
    </div>

    {{-- ===== Tool Lainnya ===== --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center">✨</span>
            <h2 class="text-lg font-bold text-slate-900">Tool Lainnya</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ([
                ['icon' => '📝', 'title' => 'Text to Image', 'desc' => 'Buat gambar dari deskripsi teks.'],
                ['icon' => '🎙️', 'title' => 'Speech to Text', 'desc' => 'Ubah rekaman suara jadi teks otomatis.'],
                ['icon' => '📚', 'title' => 'PDF Summarizer', 'desc' => 'Ringkas dokumen PDF panjang dalam sekejap.'],
            ] as $tool)
                <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 opacity-80">
                    <div class="h-12 w-12 rounded-xl bg-white flex items-center justify-center text-2xl mb-4 shadow-sm">{{ $tool['icon'] }}</div>
                    <h3 class="font-semibold text-lg text-slate-700">{{ $tool['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $tool['desc'] }}</p>
                    <span class="mt-4 inline-flex text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full px-2 py-1">Segera hadir</span>
                </div>
            @endforeach
        </div>
    </div>

</section>
@endsection
