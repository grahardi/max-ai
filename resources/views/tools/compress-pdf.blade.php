@extends('layouts.app')

@section('title', 'Perkecil Ukuran PDF - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-3xl shadow mb-3">🗜️</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Perkecil Ukuran PDF</h1>
        <p class="mt-2 text-slate-500">Kompres file PDF supaya ukurannya lebih kecil, cocok untuk dikirim lewat email/WhatsApp.</p>
    </div>

    <form action="{{ route('tools.compress-pdf.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-teal-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="pdf"
            member-input-name="member_file_id"
            accept="application/pdf"
            :eligible-files="$eligibleFiles">
            <label for="pdf"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-teal-200 hover:border-teal-400 bg-teal-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih 1 file PDF (maks 50MB)</span>
                <input id="pdf" name="pdf" type="file" accept="application/pdf" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-teal-600"></span>
            </label>
        </x-file-source-picker>

        @error('pdf')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('member_file_id')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2">Tingkat kompresi</label>
            <div class="grid grid-cols-3 gap-2">
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                    <input type="radio" name="quality" value="screen" class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">Kecil Banget</span>
                    <span class="block text-xs text-slate-400">Kualitas gambar turun</span>
                </label>
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                    <input type="radio" name="quality" value="ebook" checked class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">Seimbang</span>
                    <span class="block text-xs text-slate-400">Rekomendasi</span>
                </label>
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                    <input type="radio" name="quality" value="printer" class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">Kualitas Tinggi</span>
                    <span class="block text-xs text-slate-400">Ukuran kurang kecil</span>
                </label>
            </div>
            @error('quality')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:opacity-90 text-white font-semibold py-3 transition">
            Perkecil PDF
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-2">PDF berhasil diperkecil!</p>
            @if (session('original_size') && session('new_size'))
                <p class="text-sm text-emerald-600 mb-3">
                    {{ session('original_size') }} &rarr; <span class="font-semibold">{{ session('new_size') }}</span>
                </p>
            @endif
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download PDF
            </a>
        </div>
    @endif

</section>
@endsection
