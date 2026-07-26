<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Max AI - Kumpulan Tools AI Gratis')</title>
    <meta name="description" content="Max AI: kumpulan tools AI gratis. Hapus background foto otomatis dan tools AI lainnya, langsung dari browser.">
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
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <header class="border-b bg-white/80 backdrop-blur sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-xl text-brand-700">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-white">M</span>
                Max <span class="text-slate-800">AI</span>
            </a>
            <nav class="hidden sm:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Beranda</a>
                <a href="{{ route('tools.remove-background') }}" class="hover:text-brand-600">Remove Background</a>
                <span class="text-slate-400 cursor-not-allowed" title="Segera hadir">Tools Lainnya</span>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @if (session('success'))
            <div class="max-w-4xl mx-auto mt-4 px-4">
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-4xl mx-auto mt-4 px-4">
                <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
                    {{ session('error') }}
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
