<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberFolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\/\\\\]+$/'],
            'parent_id' => ['nullable', 'integer', 'exists:member_folders,id'],
        ], [
            'name.regex' => 'Nama folder tidak boleh mengandung karakter / atau \\.',
        ]);

        $parent = $this->authorizedParent($request, $data['parent_id'] ?? null);

        $request->user()->memberFolders()->create([
            'parent_id' => $parent?->id,
            'name' => trim($data['name']),
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function rename(Request $request, MemberFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 403);

        if ($folder->is_system) {
            return back()->with('error', 'Folder bawaan "Hasil" tidak bisa diganti nama.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\/\\\\]+$/'],
        ], [
            'name.regex' => 'Nama folder tidak boleh mengandung karakter / atau \\.',
        ]);

        $folder->update(['name' => trim($data['name'])]);

        return back()->with('success', 'Folder berhasil diganti nama.');
    }

    public function destroy(Request $request, MemberFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 403);

        if ($folder->is_system) {
            return back()->with('error', 'Folder bawaan "Hasil" tidak bisa dihapus.');
        }

        $this->deleteRecursive($folder);

        return back()->with('success', 'Folder & seluruh isinya berhasil dihapus.');
    }

    private function deleteRecursive(MemberFolder $folder): void
    {
        foreach ($folder->children as $child) {
            $this->deleteRecursive($child);
        }

        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->storage_path);
            $file->delete();
        }

        $folder->delete();
    }

    private function authorizedParent(Request $request, ?int $parentId): ?MemberFolder
    {
        if ($parentId === null) {
            return null;
        }

        $parent = MemberFolder::findOrFail($parentId);
        abort_unless($parent->user_id === $request->user()->id, 403);

        return $parent;
    }
}
