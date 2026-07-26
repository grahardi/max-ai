<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Throwable;

class ImageCompressController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function create()
    {
        return view('tools.compress-image', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::IMAGE_EXTENSIONS),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required_without:member_file_id', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'member_file_id' => ['required_without:photo', 'nullable', 'integer', 'exists:member_files,id'],
            'quality' => ['required', 'integer', 'min:10', 'max:95'],
            'max_width' => ['nullable', 'integer', 'min:200', 'max:8000'],
        ], [
            'photo.required_without' => 'Pilih foto terlebih dahulu (upload atau dari Member Area).',
            'photo.max' => 'Ukuran foto maksimal 15MB.',
        ]);

        if ($request->filled('member_file_id')) {
            $memberFile = $this->resolveMemberFile((int) $request->member_file_id, self::IMAGE_EXTENSIONS);
            $sourcePath = $this->memberFileAbsolutePath($memberFile);
            $originalName = $memberFile->original_name;
            $extension = strtolower($memberFile->extension);
        } else {
            $sourcePath = $request->file('photo')->getRealPath();
            $originalName = $request->file('photo')->getClientOriginalName();
            $extension = strtolower($request->file('photo')->getClientOriginalExtension());
        }

        $originalSize = filesize($sourcePath);

        try {
            $manager = ImageManager::gd();
            $image = $manager->read($sourcePath);

            if ($request->filled('max_width')) {
                $image->scaleDown(width: $request->integer('max_width'));
            }

            $quality = $request->integer('quality');
            $outputExtension = in_array($extension, ['png'], true) ? 'png' : 'jpg';
            $mime = $outputExtension === 'png' ? 'image/png' : 'image/jpeg';

            $filename = 'results/'.Str::uuid().'.'.$outputExtension;
            Storage::disk('public')->makeDirectory('results');
            $outputPath = Storage::disk('public')->path($filename);

            $image->save($outputPath, quality: $quality);

            $newSize = filesize($outputPath);

            $this->saveResultToMemberHasil(
                $outputPath,
                pathinfo($originalName, PATHINFO_FILENAME).'-compressed.'.$outputExtension,
                $outputExtension,
                $mime
            );

            $savedPercent = $originalSize > 0 ? round((1 - ($newSize / $originalSize)) * 100) : 0;

            return back()
                ->with('success', $savedPercent > 0
                    ? "Gambar berhasil diperkecil {$savedPercent}%!"
                    : 'Gambar berhasil diproses.')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-compressed.'.$outputExtension)
                ->with('preview_url', asset('storage/'.$filename))
                ->with('original_size', $this->humanSize($originalSize))
                ->with('new_size', $this->humanSize($newSize));
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memperkecil gambar: '.$e->getMessage());
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
