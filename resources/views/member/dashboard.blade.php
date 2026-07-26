@extends('layouts.app')

@section('title', 'Member Area - Max AI')

@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">

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

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-1.5 text-sm mb-4 flex-wrap">
        <a href="{{ route('member.dashboard') }}" class="font-medium {{ $currentFolder ? 'text-indigo-600 hover:underline' : 'text-slate-800' }}">
            📁 My Drive
        </a>
        @foreach ($breadcrumbs as $crumb)
            <span class="text-slate-300">/</span>
            <a href="{{ route('member.folder', $crumb) }}"
               class="font-medium {{ $loop->last ? 'text-slate-800' : 'text-indigo-600 hover:underline' }}">
                {{ $crumb->name }}
            </a>
        @endforeach
    </div>

    {{-- Toolbar: New Folder + Upload --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <form action="{{ route('member.folder.store') }}" method="POST" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
            <input type="text" name="name" required placeholder="Nama folder baru"
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-40 focus:border-indigo-400 focus:ring-indigo-400">
            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2">
                📁+ Buat Folder
            </button>
        </form>

        <form action="{{ route('member.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
            <label class="rounded-xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 hover:opacity-90 text-white text-sm font-medium px-4 py-2 cursor-pointer">
                📤 Upload File
                <input type="file" name="file" class="hidden" onchange="this.form.requestSubmit()">
            </label>
        </form>
    </div>

    @error('file')<p class="text-sm text-rose-600 mb-4">{{ $message }}</p>@enderror
    @error('name')<p class="text-sm text-rose-600 mb-4">{{ $message }}</p>@enderror

    <p class="text-xs text-slate-400 mb-6">
        Ekstensi didukung: {{ implode(', ', config('uploads.safe_extensions')) }}. Maks {{ round(config('uploads.max_size_kb')/1024) }}MB/file.
    </p>

    {{-- Grid Folder --}}
    @if ($subfolders->isNotEmpty())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
            @foreach ($subfolders as $folder)
                <div class="group relative rounded-xl border border-slate-200 bg-white p-4 hover:shadow-md hover:border-indigo-200 transition">
                    <a href="{{ route('member.folder', $folder) }}" class="flex items-center gap-3">
                        <span class="text-2xl">{{ $folder->is_system ? '⭐📁' : '📁' }}</span>
                        <span class="text-sm font-medium text-slate-800 truncate">{{ $folder->name }}</span>
                    </a>

                    @unless ($folder->is_system)
                        <div class="absolute top-2 right-2 hidden group-hover:flex gap-1">
                            <button type="button" onclick="toggleRename('folder-{{ $folder->id }}')"
                                    class="text-xs bg-slate-100 hover:bg-slate-200 rounded px-1.5 py-0.5" title="Rename">✏️</button>
                            <form action="{{ route('member.folder.destroy', $folder) }}" method="POST"
                                  onsubmit="return confirm('Hapus folder \'{{ $folder->name }}\' beserta semua isinya?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 rounded px-1.5 py-0.5" title="Hapus">🗑️</button>
                            </form>
                        </div>

                        <form id="folder-{{ $folder->id }}" action="{{ route('member.folder.rename', $folder) }}" method="POST"
                              class="hidden mt-2 flex gap-1">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $folder->name }}" required
                                   class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs">
                            <button type="submit" class="text-xs bg-indigo-600 text-white rounded px-2">OK</button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>
    @endif

    {{-- List File --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-5 py-3 border-b bg-slate-50 text-sm font-semibold text-slate-600">
            File di folder ini ({{ $files->count() }})
        </div>

        @forelse ($files as $file)
            <div class="px-5 py-3 border-b last:border-0 hover:bg-slate-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="h-9 w-9 flex items-center justify-center rounded-lg bg-indigo-50 text-lg shrink-0">
                            {{ $file->isImage() ? '🖼️' : ($file->isPdf() ? '📄' : '📁') }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $file->original_name }}</p>
                            <p class="text-xs text-slate-400">{{ $file->human_size }} &middot; {{ $file->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0 text-sm font-medium">
                        <a href="{{ route('member.download', $file) }}" class="text-indigo-600 hover:underline">Download</a>
                        <button type="button" onclick="toggleRename('file-{{ $file->id }}')" class="text-slate-500 hover:underline">Rename</button>
                        <form action="{{ route('member.destroy', $file) }}" method="POST" onsubmit="return confirm('Hapus file ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
                <form id="file-{{ $file->id }}" action="{{ route('member.rename', $file) }}" method="POST" class="hidden mt-2 flex gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="name" value="{{ $file->original_name }}" required
                           class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
                    <button type="submit" class="text-sm bg-indigo-600 text-white rounded-lg px-3">Simpan</button>
                </form>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-400">Folder ini masih kosong.</p>
        @endforelse
    </div>

</section>

<script>
    function toggleRename(id) {
        document.getElementById(id).classList.toggle('hidden');
    }
</script>
@endsection
