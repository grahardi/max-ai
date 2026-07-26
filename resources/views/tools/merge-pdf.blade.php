@extends('layouts.app')

@section('title', 'Gabung PDF - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 flex items-center justify-center text-3xl shadow mb-3">🧩</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Gabung PDF (Merge)</h1>
        <p class="mt-2 text-slate-500">Pilih minimal 2 file PDF, urutan hasil gabungan mengikuti urutan pemilihan file.</p>
    </div>

    <form action="{{ route('tools.merge-pdf.process') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-rose-100 bg-white p-6 space-y-4">
        @csrf

        <label for="pdfs"
               class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-rose-200 hover:border-rose-400 bg-rose-50/40 py-10 cursor-pointer transition">
            <span class="text-3xl">📤</span>
            <span class="text-sm text-slate-500">Klik untuk pilih minimal 2 file PDF (maks 20MB/file)</span>
            <input id="pdfs" name="pdfs[]" type="file" accept="application/pdf" multiple class="hidden"
                   onchange="document.getElementById('file-count').innerText = this.files.length + ' PDF dipilih'">
            <span id="file-count" class="text-xs font-medium text-rose-600"></span>
        </label>

        @error('pdfs')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('pdfs.*')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror

        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-rose-500 to-orange-500 hover:opacity-90 text-white font-semibold py-3 transition">
            Gabungkan PDF
        </button>
    </form>

    @if (session('download_url'))
        <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <p class="text-emerald-700 font-medium mb-3">PDF gabungan sudah siap!</p>
            <a href="{{ session('download_url') }}" download="{{ session('download_name') }}"
               class="inline-flex rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-6 py-2.5">
                Download PDF
            </a>
        </div>
    @endif

</section>
@endsection
