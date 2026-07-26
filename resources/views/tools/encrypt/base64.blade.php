@extends('layouts.app')

@section('title', 'Base64 - Max AI')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="text-center mb-8">
        <div class="h-14 w-14 mx-auto rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-3xl shadow mb-3">🔡</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Base64</h1>
        <p class="mt-2 text-slate-500">Encode teks jadi Base64, atau decode Base64 balik jadi teks asli.</p>
    </div>

    <div class="rounded-2xl border border-violet-100 bg-white p-6">

        <div class="flex gap-2 mb-5 text-sm font-semibold">
            <a href="?tab=encode" class="px-3 py-1.5 rounded-lg {{ request('tab', old('action', 'encode')) === 'encode' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-600' }}">Encode</a>
            <a href="?tab=decode" class="px-3 py-1.5 rounded-lg {{ request('tab', old('action')) === 'decode' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-600' }}">Decode</a>
        </div>

        <form action="{{ route('tools.encrypt.base64.process') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="{{ request('tab', old('action', 'encode')) === 'decode' ? 'decode' : 'encode' }}">

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">
                    {{ request('tab', old('action')) === 'decode' ? 'Teks Base64' : 'Teks asli' }}
                </label>
                <textarea name="text" rows="5" required
                          class="w-full rounded-xl border border-slate-200 p-3 text-sm font-mono focus:border-violet-400 focus:ring-violet-400">{{ old('text') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 hover:opacity-90 text-white font-semibold py-3 transition">
                {{ request('tab', old('action')) === 'decode' ? 'Decode' : 'Encode' }}
            </button>
        </form>

        @if (session('result') !== null)
            <div class="mt-6">
                <p class="text-sm font-medium text-slate-500 mb-1">Hasil</p>
                <textarea readonly rows="5"
                          class="w-full rounded-xl bg-violet-50 border border-violet-200 p-3 text-sm font-mono">{{ session('result') }}</textarea>
            </div>
        @endif

    </div>

</section>
@endsection
