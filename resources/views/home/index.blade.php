@extends('layouts.app')

@section('title', 'Max AI - Kumpulan Tools AI Gratis')

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 pt-14 pb-10 text-center">
    <span class="inline-block rounded-full bg-brand-100 text-brand-700 text-xs font-semibold px-3 py-1 mb-4">
        ✨ Kumpulan AI Tools dalam satu tempat
    </span>
    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
        Tools AI yang bikin kerjaan <span class="text-brand-600">lebih cepat selesai</span>
    </h1>
    <p class="mt-4 text-slate-500 max-w-2xl mx-auto">
        Mulai dari hapus background foto otomatis, dan akan terus bertambah dengan tools AI baru lainnya. Gratis, cepat, tanpa install apapun.
    </p>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Tool 1: Remove Background (aktif) --}}
        <a href="{{ route('tools.remove-background') }}"
           class="group rounded-2xl border border-slate-200 bg-white p-6 hover:shadow-lg hover:border-brand-300 transition">
            <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-2xl mb-4">🖼️</div>
            <h3 class="font-semibold text-lg text-slate-900 group-hover:text-brand-600">Remove Background</h3>
            <p class="mt-1 text-sm text-slate-500">
                Hapus background foto secara otomatis dengan AI, hasil PNG transparan siap pakai.
            </p>
            <span class="mt-4 inline-flex text-sm font-medium text-brand-600">Coba sekarang &rarr;</span>
        </a>

        {{-- Placeholder tools mendatang --}}
        @foreach ([
            ['icon' => '📝', 'title' => 'Text to Image', 'desc' => 'Buat gambar dari deskripsi teks.'],
            ['icon' => '🎙️', 'title' => 'Speech to Text', 'desc' => 'Ubah rekaman suara jadi teks otomatis.'],
            ['icon' => '📄', 'title' => 'PDF Summarizer', 'desc' => 'Ringkas dokumen PDF panjang dalam sekejap.'],
        ] as $tool)
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 opacity-70">
                <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-2xl mb-4">{{ $tool['icon'] }}</div>
                <h3 class="font-semibold text-lg text-slate-700">{{ $tool['title'] }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $tool['desc'] }}</p>
                <span class="mt-4 inline-flex text-xs font-medium text-slate-400 bg-slate-200 rounded-full px-2 py-1">Segera hadir</span>
            </div>
        @endforeach

    </div>
</section>
@endsection
