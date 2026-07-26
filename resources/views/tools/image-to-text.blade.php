@extends('layouts.app')

@section('title', 'Gambar ke Teks (OCR) - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-3xl shadow mb-3">🔤</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Gambar ke Teks (OCR)</h1>
        <p class="mt-2 text-slate-500">Upload foto tulisan atau hasil scan, teksnya akan diekstrak otomatis.</p>
    </div>

    <form action="{{ route('tools.image-to-text.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-fuchsia-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="photo"
            member-input-name="member_file_id"
            accept="image/png,image/jpeg,image/webp"
            :eligible-files="$eligibleFiles">
            <label for="photo"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-fuchsia-200 hover:border-fuchsia-400 bg-fuchsia-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih foto (JPG, PNG, WEBP - maks 8MB)</span>
                <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-fuchsia-600"></span>
            </label>
        </x-file-source-picker>

        @error('photo')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('member_file_id')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-fuchsia-500 to-pink-600 hover:opacity-90 text-white font-semibold py-3 transition">
            Ekstrak Teks
        </button>
    </form>

    @if (session('extracted_text') !== null)
        <div class="mt-8 grid sm:grid-cols-2 gap-6">
            @if (session('preview_url'))
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-2">Foto</p>
                    <img src="{{ session('preview_url') }}" class="rounded-xl border border-slate-200 w-full object-contain" alt="Foto">
                </div>
            @endif
            <div>
                <p class="text-sm font-medium text-slate-500 mb-2">Hasil Teks</p>
                <textarea readonly rows="10"
                          class="w-full rounded-xl border border-slate-200 p-3 text-sm text-slate-700 bg-slate-50">{{ session('extracted_text') ?: '(Tidak ada teks yang terdeteksi)' }}</textarea>
            </div>
        </div>
    @endif

</section>
@endsection
