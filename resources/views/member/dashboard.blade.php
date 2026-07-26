@extends('layouts.app')

@section('title', 'Member Area - Max AI')

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Halo, {{ auth()->user()->name }} 👋</h1>
            <p class="text-sm text-slate-500">File kamu, aman tersimpan dan bisa diakses kapan saja.</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Logout</button>
        </form>
    </div>

    {{-- Kuota --}}
    @php
        $percent = $quotaBytes > 0 ? min(100, round(($usedBytes / $quotaBytes) * 100)) : 0;
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-5 mb-6">
        <div class="flex justify-between text-sm mb-2">
            <span class="font-medium text-slate-600">Penyimpanan terpakai</span>
            <span class="text-slate-500">{{ number_format($usedBytes / 1048576, 1) }} MB / {{ number_format($quotaBytes / 1048576, 0) }} MB</span>
        </div>
        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-fuchsia-500" style="width: {{ $percent }}%"></div>
        </div>
    </div>

    {{-- Upload --}}
    <form action="{{ route('member.upload') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-indigo-100 bg-white p-6 mb-8 space-y-4">
        @csrf
        <label for="file"
               class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 bg-indigo-50/40 py-8 cursor-pointer transition">
            <span class="text-3xl">📤</span>
            <span class="text-sm text-slate-500">Klik untuk upload file</span>
            <input id="file" name="file" type="file" class="hidden"
                   onchange="document.getElementById('file-name').innerText = this.files[0]?.name ?? ''">
            <span id="file-name" class="text-xs font-medium text-indigo-600"></span>
        </label>
        <p class="text-xs text-slate-400 text-center">
            Ekstensi didukung: {{ implode(', ', config('uploads.safe_extensions')) }}. Maks {{ round(config('uploads.max_size_kb')/1024) }}MB/file.
        </p>
        @error('file')<p class="text-sm text-rose-600 text-center">{{ $message }}</p>@enderror
        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 hover:opacity-90 text-white font-semibold py-3 transition">
            Upload File
        </button>
    </form>

    {{-- List file --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-5 py-3 border-b bg-slate-50 text-sm font-semibold text-slate-600">
            File Kamu ({{ $files->total() }})
        </div>

        @forelse ($files as $file)
            <div class="flex items-center justify-between px-5 py-3 border-b last:border-0 hover:bg-slate-50">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="h-9 w-9 flex items-center justify-center rounded-lg bg-indigo-50 text-lg shrink-0">
                        {{ $file->isImage() ? '🖼️' : ($file->isPdf() ? '📄' : '📁') }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $file->original_name }}</p>
                        <p class="text-xs text-slate-400">{{ $file->human_size }} &middot; {{ $file->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('member.download', $file) }}" class="text-sm font-medium text-indigo-600 hover:underline">Download</a>
                    <form action="{{ route('member.destroy', $file) }}" method="POST"
                          onsubmit="return confirm('Hapus file ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-rose-500 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada file yang diupload.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $files->links() }}
    </div>

</section>
@endsection
