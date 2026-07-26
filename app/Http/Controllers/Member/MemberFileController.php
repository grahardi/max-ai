<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberFileController extends Controller
{
    public function index(Request $request): View
    {
        $files = $request->user()
            ->memberFiles()
            ->latest()
            ->paginate(15);

        $usedBytes = $request->user()->memberFiles()->sum('size');
        $quotaBytes = config('uploads.quota_mb_per_user') * 1024 * 1024;

        return view('member.dashboard', [
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
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.max' => 'Ukuran file maksimal '.round($maxSizeKb / 1024).'MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Guard tambahan: tolak kalau ekstensi bukan whitelist (double-check di luar rule)
        if (! in_array($extension, $safeExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => 'Ekstensi .'.$extension.' tidak diizinkan.',
            ]);
        }

        $user = $request->user();
        $usedBytes = $user->memberFiles()->sum('size');
        $quotaBytes = config('uploads.quota_mb_per_user') * 1024 * 1024;

        if ($usedBytes + $file->getSize() > $quotaBytes) {
            return back()->with('error', 'Kuota penyimpanan kamu penuh. Hapus beberapa file dulu.');
        }

        $storedName = Str::uuid().'.'.$extension;

        $file->storeAs('members/'.$user->id, $storedName, 'public');

        MemberFile::create([
            'user_id' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'File berhasil diupload!');
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
}
