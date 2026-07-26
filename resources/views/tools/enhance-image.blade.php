@extends('layouts.app')

@section('title', 'Perjelas & Perbesar Kualitas Gambar - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-amber-500 to-pink-500 flex items-center justify-center text-3xl shadow mb-3">✨</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Perjelas & Perbesar Kualitas Gambar</h1>
        <p class="mt-2 text-slate-500">Perbesar resolusi gambar sekaligus mempertajam detailnya. Cocok untuk foto buram atau resolusi kecil.</p>
    </div>

    <form action="{{ route('tools.enhance-image.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-amber-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="photo"
            member-input-name="member_file_id"
            accept="image/png,image/jpeg,image/webp"
            :eligible-files="$eligibleFiles">
            <label for="photo"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-amber-200 hover:border-amber-400 bg-amber-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih foto (JPG, PNG, WEBP - maks 8MB)</span>
                <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-amber-600"></span>
            </label>
        </x-file-source-picker>

        @error('photo')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        @error('member_file_id')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2">Perbesar berapa kali?</label>
            <div class="grid grid-cols-3 gap-2">
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="scale" value="1.5" class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">1.5x</span>
                </label>
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="scale" value="2" checked class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">2x</span>
                </label>
                <label class="border border-slate-200 rounded-xl p-3 text-center cursor-pointer has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                    <input type="radio" name="scale" value="3" class="hidden">
                    <span class="block text-sm font-semibold text-slate-700">3x</span>
                </label>
            </div>
            @error('scale')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-pink-500 hover:opacity-90 text-white font-semibold py-3 transition">
            Perjelas & Perbesar
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-3">Gambar berhasil diperjelas & diperbesar!</p>
            <img src="{{ session('preview_url') }}" class="mx-auto max-h-64 rounded-xl border border-emerald-200 mb-4" alt="Hasil">
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download Gambar
            </a>
        </div>
    @endif

</section>
@endsection
