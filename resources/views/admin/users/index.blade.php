@extends('layouts.app')

@section('title', 'Admin - Kelola User - Max AI')

@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">👑 Admin Panel</h1>
            <p class="text-sm text-slate-500">Kelola user, setujui pendaftaran baru.</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Logout</button>
        </form>
    </div>

    @php $pendingCount = $users->where('is_approved', false)->count(); @endphp
    @if ($pendingCount > 0)
        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm font-medium mb-6">
            ⏳ Ada {{ $pendingCount }} pendaftar menunggu persetujuan di halaman ini.
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-slate-500 border-b">
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Username</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Daftar</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-slate-400">(kamu)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->username ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $user->isAdmin() ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $user->isAdmin() ? 'Admin' : 'User' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->is_approved)
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Disetujui</span>
                            @else
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $user->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                @unless ($user->is_approved)
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 py-1.5">
                                            ✅ Setujui
                                        </button>
                                    </form>
                                @endunless

                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.role', $user) }}" method="POST"
                                          onsubmit="return confirm('Ubah role {{ $user->name }} jadi {{ $user->isAdmin() ? 'User' : 'Admin' }}?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg px-3 py-1.5">
                                            {{ $user->isAdmin() ? '⬇️ Jadikan User' : '⬆️ Jadikan Admin' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Hapus user {{ $user->name }}? Semua file miliknya juga akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-medium bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg px-3 py-1.5">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

</section>
@endsection
