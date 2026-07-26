<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByRaw('is_approved asc') // yang pending muncul duluan
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', ['users' => $users]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return back()->with('success', "Akun {$user->name} berhasil disetujui.");
    }

    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Kamu tidak bisa mengubah role akun sendiri.');
        }

        $user->update([
            'role' => $user->role === 'admin' ? 'user' : 'admin',
        ]);

        return back()->with('success', "Role {$user->name} berhasil diubah menjadi {$user->role}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus admin terakhir.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
