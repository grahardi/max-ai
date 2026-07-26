<?php

namespace App\Http\Controllers\Tools\Concerns;

use App\Models\MemberFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

trait UsesMemberFileSource
{
    /**
     * File milik member yang login, difilter berdasarkan ekstensi, untuk ditampilkan
     * sebagai pilihan "Pilih dari File Manager" di form tool.
     */
    protected function eligibleMemberFiles(array $extensions): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()->memberFiles()
            ->whereIn('extension', $extensions)
            ->latest()
            ->get();
    }

    /**
     * Ambil & validasi satu file dari Member Area milik user yang login.
     */
    protected function resolveMemberFile(int $id, array $allowedExtensions): MemberFile
    {
        $file = MemberFile::findOrFail($id);

        abort_unless($file->user_id === Auth::id(), 403);

        if (! in_array(strtolower($file->extension), $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'member_file_id' => 'File yang dipilih bukan format yang didukung untuk tool ini.',
            ]);
        }

        return $file;
    }

    protected function memberFileAbsolutePath(MemberFile $file): string
    {
        return Storage::disk('public')->path($file->storage_path);
    }
}
