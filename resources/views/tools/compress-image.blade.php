@extends('layouts.app')

@section('title', 'Perkecil Ukuran Gambar - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center text-3xl shadow mb-3">🗜️</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Perkecil Ukuran Gambar</h1>
        <p class="mt-2 text-slate-500">Kompres foto supaya ukuran filenya lebih kecil, cocok untuk upload ke website atau kirim lewat chat.</p>
    </div>

    <form action="{{ route('tools.compress-image.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-cyan-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="photo"
            member-input-name="member_file_id"
            accept="image/png,image/jpeg,image/webp"
            :eligible-files="$eligibleFiles">
            <label for="photo"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-cyan-200 hover:border-cyan-400 bg-cyan-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih foto (JPG, PNG, WEBP - maks 15MB)</span>
                <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-cyan-600"></span>
            </label>
        </x-file-source-picker>

        @error('photo')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        @error('member_file_id')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">
                Kualitas: <span id="quality-value">75</span>%
            </label>
            <input type="range" name="quality" min="10" max="95" value="75"
                   oninput="document.getElementById('quality-value').innerText = this.value"
                   class="w-full accent-cyan-600">
            <p class="text-xs text-slate-400 mt-1">Makin kecil persentase, makin kecil ukuran file tapi kualitas gambar makin turun.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Lebar maksimal (opsional)</label>
            <select name="max_width" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Jangan diubah (pakai ukuran asli)</option>
                <option value="1920">1920px (Full HD)</option>
                <option value="1280">1280px (HD)</option>
                <option value="800">800px</option>
                <option value="500">500px</option>
            </select>
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:opacity-90 text-white font-semibold py-3 transition">
            Perkecil Gambar
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-2">Gambar berhasil diperkecil!</p>
            @if (session('original_size') && session('new_size'))
                <p class="text-sm text-emerald-600 mb-3">
                    {{ session('original_size') }} &rarr; <span class="font-semibold">{{ session('new_size') }}</span>
                </p>
            @endif
            <img src="{{ session('preview_url') }}" class="mx-auto max-h-64 rounded-xl border border-emerald-200 mb-4" alt="Hasil">
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download Gambar
            </a>
        </div>
    @endif

</section>
@endsection
