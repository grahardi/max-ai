<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberFile;
use App\Models\MemberFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MemberFileController extends Controller
{
    /**
     * Tampilkan isi folder (root kalau folder=null), ala Google Drive.
     */
    public function index(Request $request, ?MemberFolder $folder = null): View
    {
        $user = $request->user();

        if ($folder) {
            abort_unless($folder->user_id === $user->id, 403);
        }

        $subfolders = $user->memberFolders()
            ->where('parent_id', $folder?->id)
            ->orderBy('name')
            ->get();

        $files = $user->memberFiles()
            ->where('folder_id', $folder?->id)
            ->latest()
            ->get();

        $usedBytes = $user->memberFiles()->sum('size');
        $quotaBytes = config('uploads.quota_mb_per_user') * 1024 * 1024;

        return view('member.dashboard', [
            'currentFolder' => $folder,
            'breadcrumbs' => $folder ? $folder->breadcrumbs() : [],
            'subfolders' => $subfolders,
            'files' => $files,
            'usedBytes' => $usedBytes,
            'quotaBytes' => $quotaBytes,
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $safeExtensions = config('uploads.safe_extensions');
        $maxSizeKb = config('uploads.max_size_kb');

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.$maxSizeKb,
                function ($attribute, $value, $fail) use ($safeExtensions) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, $safeExtensions, true)) {
                        $fail('Ekstensi .'.$ext.' tidak diizinkan demi keamanan. Ekstensi yang didukung: '.implode(', ', $safeExtensions).'.');
                    }
                },
            ],
            'folder_id' => ['nullable', 'integer', 'exists:member_folders,id'],
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.max' => 'Ukuran file maksimal '.round($maxSizeKb / 1024).'MB.',
        ]);

        $user = $request->user();
        $folder = $this->authorizedFolder($request, $request->integer('folder_id') ?: null);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $usedBytes = $user->memberFiles()->sum('size');
        $quotaBytes = config('uploads.quota_mb_per_user') * 1024 * 1024;

        if ($usedBytes + $file->getSize() > $quotaBytes) {
            return back()->with('error', 'Kuota penyimpanan kamu penuh. Hapus beberapa file dulu.');
        }

        $storedName = Str::uuid().'.'.$extension;

        $file->storeAs('members/'.$user->id, $storedName, 'public');

        MemberFile::create([
            'user_id' => $user->id,
            'folder_id' => $folder?->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'File berhasil diupload!');
    }

    public function rename(Request $request, MemberFile $memberFile): RedirectResponse
    {
        abort_unless($memberFile->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'regex:/^[^\/\\\\]+$/'],
        ], [
            'name.regex' => 'Nama file tidak boleh mengandung karakter / atau \\.',
        ]);

        $newName = trim($data['name']);

        // Pastikan ekstensi tetap konsisten dengan file aslinya
        if (! str_ends_with(strtolower($newName), '.'.$memberFile->extension)) {
            $newName .= '.'.$memberFile->extension;
        }

        $memberFile->update(['original_name' => $newName]);

        return back()->with('success', 'File berhasil diganti nama.');
    }

    public function download(Request $request, MemberFile $memberFile): Response
    {
        abort_unless($memberFile->user_id === $request->user()->id, 403);

        $path = Storage::disk('public')->path($memberFile->storage_path);
        abort_unless(file_exists($path), 404);

        return response()->download($path, $memberFile->original_name);
    }

    public function destroy(Request $request, MemberFile $memberFile): RedirectResponse
    {
        abort_unless($memberFile->user_id === $request->user()->id, 403);

        Storage::disk('public')->delete($memberFile->storage_path);
        $memberFile->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }

    private function authorizedFolder(Request $request, ?int $folderId): ?MemberFolder
    {
        if ($folderId === null) {
            return null;
        }

        $folder = MemberFolder::findOrFail($folderId);
        abort_unless($folder->user_id === $request->user()->id, 403);

        return $folder;
    }
}
