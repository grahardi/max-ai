<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Max AI - Kumpulan Tools AI Gratis')</title>
    <meta name="description" content="Max AI: kumpulan tools AI gratis. Hapus background foto, gabung/pecah PDF, gambar ke PDF, gambar ke teks (OCR), dan tools AI lainnya.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f2f6ff', 100: '#e3ecff', 500: '#4f6df5',
                            600: '#3d55e0', 700: '#2f42b3',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .dropdown:hover .dropdown-menu { display: block; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <header class="bg-white border-b sticky top-0 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-xl">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 via-fuchsia-600 to-rose-500 text-white shadow">M</span>
                <span class="bg-gradient-to-r from-indigo-600 via-fuchsia-600 to-rose-500 bg-clip-text text-transparent">Max AI</span>
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
                <a href="{{ route('home') }}"
                   class="px-3 py-2 rounded-lg hover:bg-slate-100 {{ request()->routeIs('home') ? 'text-brand-600' : 'text-slate-600' }}">
                    Home
                </a>

                <div class="relative dropdown">
                    <button class="px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center gap-1">
                        🖼️ Proses Gambar <span class="text-xs">▾</span>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 top-full pt-2 w-56">
                        <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                            <a href="{{ route('tools.remove-background') }}" class="block px-4 py-2.5 text-sm hover:bg-indigo-50 hover:text-indigo-600">Remove Background</a>
                            <a href="{{ route('tools.image-to-pdf') }}" class="block px-4 py-2.5 text-sm hover:bg-indigo-50 hover:text-indigo-600">Gambar ke PDF</a>
                            <a href="{{ route('tools.image-to-text') }}" class="block px-4 py-2.5 text-sm hover:bg-indigo-50 hover:text-indigo-600">Gambar ke Teks (OCR)</a>
                        </div>
                    </div>
                </div>

                <div class="relative dropdown">
                    <button class="px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center gap-1">
                        📄 Proses PDF <span class="text-xs">▾</span>
                    </button>
                    <div class="dropdown-menu hidden absolute left-0 top-full pt-2 w-56">
                        <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                            <a href="{{ route('tools.merge-pdf') }}" class="block px-4 py-2.5 text-sm hover:bg-rose-50 hover:text-rose-600">Gabung PDF (Merge)</a>
                            <a href="{{ route('tools.split-pdf') }}" class="block px-4 py-2.5 text-sm hover:bg-rose-50 hover:text-rose-600">Pecah PDF (Split)</a>
                        </div>
                    </div>
                </div>

                <div class="relative dropdown">
                    <button class="px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center gap-1">
                        ✨ Tool Lainnya <span class="text-xs">▾</span>
                    </button>
                    <div class="dropdown-menu hidden absolute right-0 top-full pt-2 w-56">
                        <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                            <span class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-400">
                                Text to Image <span class="text-[10px] bg-slate-100 rounded-full px-2 py-0.5">Segera</span>
                            </span>
                            <span class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-400">
                                Speech to Text <span class="text-[10px] bg-slate-100 rounded-full px-2 py-0.5">Segera</span>
                            </span>
                            <span class="flex items-center justify-between px-4 py-2.5 text-sm text-slate-400">
                                PDF Summarizer <span class="text-[10px] bg-slate-100 rounded-full px-2 py-0.5">Segera</span>
                            </span>
                        </div>
                    </div>
                </div>
            </nav>

            <a href="{{ route('home') }}#tools"
               class="md:hidden text-sm font-semibold text-brand-600">Menu</a>
        </div>
    </header>

    <main class="flex-1">
        @if (session('success'))
            <div class="max-w-4xl mx-auto mt-4 px-4">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">
                    ✅ {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-4xl mx-auto mt-4 px-4">
                <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">
                    ⚠️ {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t bg-white mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 text-sm text-slate-500 flex flex-col sm:flex-row justify-between gap-2">
            <p>&copy; {{ date('Y') }} Max AI. Dibangun dengan Laravel.</p>
            <p>Fitur baru akan terus ditambahkan 🚀</p>
        </div>
    </footer>

</body>
</html>
