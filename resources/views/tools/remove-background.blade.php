@extends('layouts.app')

@section('title', 'Remove Background Foto - Max AI')

@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Remove Background Foto</h1>
        <p class="mt-2 text-slate-500">Upload foto, AI akan menghapus background secara otomatis. Hasil PNG transparan.</p>
    </div>

    <form action="{{ route('tools.remove-background.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="photo"
            member-input-name="member_file_id"
            accept="image/png,image/jpeg,image/webp"
            :eligible-files="$eligibleFiles">
            <label for="photo"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 hover:border-brand-400 bg-slate-50 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih foto (JPG, PNG, WEBP - maks 8MB)</span>
                <input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-brand-600"></span>
            </label>
        </x-file-source-picker>

        @error('photo')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('member_file_id')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition">
            Hapus Background
        </button>
    </form>

    @if (session('result'))
        @php($result = session('result'))
        @php($displayUrl = session('composited_url') ?? $result->result_url)
        <div class="mt-10 grid sm:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-slate-500 mb-2">Foto Asli</p>
                <img src="{{ $result->original_url }}" class="rounded-xl border border-slate-200 w-full object-contain" alt="Foto asli">
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-2">Hasil</p>
                <div class="rounded-xl border border-slate-200 p-2"
                     style="background-image: linear-gradient(45deg,#eee 25%,transparent 25%),linear-gradient(-45deg,#eee 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#eee 75%),linear-gradient(-45deg,transparent 75%,#eee 75%); background-size:20px 20px; background-position:0 0,0 10px,10px -10px,-10px 0;">
                    <img src="{{ $displayUrl }}" class="w-full object-contain" alt="Hasil remove background">
                </div>
                <a href="{{ $displayUrl }}" download
                   class="mt-3 inline-flex w-full justify-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium py-2.5">
                    Download PNG
                </a>
            </div>
        </div>

        {{-- Ganti Background --}}
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-sm font-semibold text-slate-700 mb-1">🎨 Ganti Background</p>
            <p class="text-xs text-slate-400 mb-4">Pilih warna solid, atau upload gambar background sendiri. Bisa ganti berkali-kali, hasil selalu diambil dari potongan transparan asli.</p>

            <form action="{{ route('tools.remove-background.background') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="processed_image_id" value="{{ $result->id }}">

                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Warna solid</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach (['#ffffff' => 'Putih', '#000000' => 'Hitam', '#ef4444' => 'Merah', '#3b82f6' => 'Biru', '#22c55e' => 'Hijau', '#f59e0b' => 'Kuning', '#94a3b8' => 'Abu-abu'] as $hex => $label)
                            <button type="submit" name="color" value="{{ $hex }}" title="{{ $label }}"
                                    class="h-9 w-9 rounded-full border-2 border-slate-200 hover:border-brand-500 transition"
                                    style="background-color: {{ $hex }}"></button>
                        @endforeach

                        <label class="h-9 w-9 rounded-full border-2 border-dashed border-slate-300 hover:border-brand-500 flex items-center justify-center cursor-pointer text-slate-400 text-xs relative overflow-hidden">
                            🎨
                            <input type="color" name="color" onchange="this.form.requestSubmit()"
                                   class="absolute inset-0 opacity-0 cursor-pointer">
                        </label>
                    </div>
                </div>

                <div class="pt-2 border-t">
                    <p class="text-xs font-medium text-slate-500 mb-2">Atau upload gambar background sendiri</p>
                    <input type="file" name="bg_image" accept="image/png,image/jpeg,image/webp"
                           onchange="this.form.requestSubmit()"
                           class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-600 file:text-xs file:font-medium hover:file:bg-brand-100">
                </div>
            </form>
            @error('color')<p class="text-sm text-rose-600 mt-2">{{ $message }}</p>@enderror
            @error('bg_image')<p class="text-sm text-rose-600 mt-2">{{ $message }}</p>@enderror
        </div>
    @endif

    @if (auth()->check())
        @if ($recent->isNotEmpty())
            <div class="mt-14">
                <p class="text-sm font-medium text-slate-500 mb-3">Riwayat kamu (folder Hasil)</p>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    @foreach ($recent as $item)
                        <a href="{{ $item->url }}" download="{{ $item->original_name }}">
                            <img src="{{ $item->url }}" class="aspect-square rounded-lg border border-slate-200 object-cover bg-slate-100" alt="Riwayat">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <p class="mt-10 text-center text-sm text-slate-400">
            <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:underline">Daftar sebagai member</a>
            untuk menyimpan riwayat hasil proses kamu secara privat.
        </p>
    @endif

</section>
@endsection
