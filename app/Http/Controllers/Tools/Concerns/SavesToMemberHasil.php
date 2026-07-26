<?php

namespace App\Http\Controllers\Tools\Concerns;

use App\Models\MemberFile;
use App\Models\MemberFolder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

trait SavesToMemberHasil
{
    /**
     * Simpan salinan hasil proses tool ke folder "Hasil" milik member yang sedang login.
     * Aman dipanggil walau user guest (tidak melakukan apapun) atau kalau gagal (tidak
     * mengganggu alur utama tool).
     */
    protected function saveResultToMemberHasil(string $sourceAbsolutePath, string $displayName, string $extension, ?string $mimeType = null): void
    {
        if (! Auth::check()) {
            return;
        }

        try {
            $user = Auth::user();

            $hasilFolder = MemberFolder::firstOrCreate(
                ['user_id' => $user->id, 'is_system' => true, 'name' => 'Hasil'],
                ['parent_id' => null]
            );

            $storedName = Str::uuid().'.'.$extension;
            $relativePath = 'members/'.$user->id.'/'.$storedName;

            Storage::disk('public')->makeDirectory('members/'.$user->id);
            Storage::disk('public')->put($relativePath, file_get_contents($sourceAbsolutePath));

            MemberFile::create([
                'user_id' => $user->id,
                'folder_id' => $hasilFolder->id,
                'original_name' => $displayName,
                'stored_name' => $storedName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => filesize($sourceAbsolutePath) ?: 0,
            ]);
        } catch (Throwable $e) {
            // Sengaja diabaikan: kegagalan simpan ke Hasil tidak boleh menggagalkan proses tool utama.
            report($e);
        }
    }
}
