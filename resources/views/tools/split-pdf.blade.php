@extends('layouts.app')

@section('title', 'Pecah PDF - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 flex items-center justify-center text-3xl shadow mb-3">✂️</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Pecah PDF (Split)</h1>
        <p class="mt-2 text-slate-500">Upload 1 file PDF, tiap halaman akan dipecah jadi file PDF terpisah dalam 1 ZIP.</p>
    </div>

    <form action="{{ route('tools.split-pdf.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-orange-100 bg-white p-6 space-y-4">
        @csrf

        <x-file-source-picker
            :multiple="false"
            input-name="pdf"
            member-input-name="member_file_id"
            accept="application/pdf"
            :eligible-files="$eligibleFiles">
            <label for="pdf"
                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-orange-200 hover:border-orange-400 bg-orange-50/40 py-10 cursor-pointer transition">
                <span class="text-3xl">📤</span>
                <span class="text-sm text-slate-500">Klik untuk pilih 1 file PDF (maks 20MB)</span>
                <input id="pdf" name="pdf" type="file" accept="application/pdf" class="hidden"
                       onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
                <span id="file-name" class="text-xs font-medium text-orange-600"></span>
            </label>
        </x-file-source-picker>

        @error('pdf')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('member_file_id')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:opacity-90 text-white font-semibold py-3 transition">
            Pecah PDF
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-3">File ZIP hasil split sudah siap!</p>
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download ZIP
            </a>
        </div>
    @endif

</section>
@endsection
