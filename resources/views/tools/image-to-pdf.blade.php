@extends('layouts.app')

@section('title', 'Gambar ke PDF - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 flex items-center justify-center text-3xl shadow mb-3">📎</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Gambar ke PDF</h1>
        <p class="mt-2 text-slate-500">Upload satu atau beberapa foto, langsung digabung jadi satu file PDF.</p>
    </div>

    <form action="{{ route('tools.image-to-pdf.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-indigo-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="true"
            input-name="photos[]"
            member-input-name="member_file_ids[]"
            accept="image/png,image/jpeg,image/webp"
            :eligible-files="$eligibleFiles">
            <label for="photos"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 bg-indigo-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih beberapa foto (JPG, PNG, WEBP - maks 8MB/foto)</span>
                <input id="photos" name="photos[]" type="file" accept="image/png,image/jpeg,image/webp" multiple class="hidden"
                       onchange="document.getElementById('file-count').innerText = this.files.length + ' foto dipilih'">
                <span id="file-count" class="text-xs font-medium text-indigo-600"></span>
            </label>
        </x-file-source-picker>

        @error('photos')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('photos.*')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('member_file_ids')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:opacity-90 text-white font-semibold py-3 transition">
            Jadikan PDF
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-3">PDF kamu sudah siap!</p>
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download PDF
            </a>
        </div>
    @endif

</section>
@endsection
