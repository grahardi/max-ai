<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

class ImageToTextController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function create()
    {
        return view('tools.image-to-text', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::IMAGE_EXTENSIONS),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required_without:member_file_id', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'member_file_id' => ['required_without:photo', 'nullable', 'integer', 'exists:member_files,id'],
        ], [
            'photo.required_without' => 'Silakan pilih foto atau file dari Member Area.',
            'photo.image' => 'File yang diupload harus berupa gambar.',
            'photo.mimes' => 'Format yang didukung: JPG, JPEG, PNG, WEBP.',
            'photo.max' => 'Ukuran foto maksimal 8MB.',
        ]);

        if ($request->filled('member_file_id')) {
            $memberFile = $this->resolveMemberFile((int) $request->member_file_id, self::IMAGE_EXTENSIONS);
            $sourceAbsolutePath = $this->memberFileAbsolutePath($memberFile);
            $path = 'uploads/'.Str::uuid().'.'.$memberFile->extension;
            Storage::disk('public')->put($path, file_get_contents($sourceAbsolutePath));
        } else {
            $path = $request->file('photo')->store('uploads', 'public');
        }

        $fullPath = Storage::disk('public')->path($path);

        try {
            $text = (new TesseractOCR($fullPath))
                ->lang('ind', 'eng')
                ->run();

            $text = trim($text);

            // Simpan hasil ekstraksi sebagai file .txt ke folder Hasil member (kalau login)
            $tmpTxtPath = storage_path('app/tmp-ocr-'.Str::uuid().'.txt');
            file_put_contents($tmpTxtPath, $text);
            $this->saveResultToMemberHasil($tmpTxtPath, 'hasil-ocr.txt', 'txt', 'text/plain');
            @unlink($tmpTxtPath);

            return back()
                ->with('success', 'Teks berhasil diekstrak dari gambar!')
                ->with('extracted_text', $text)
                ->with('preview_url', asset('storage/'.$path));
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal membaca teks dari gambar. Pastikan Tesseract OCR sudah terinstall di server: sudo apt install tesseract-ocr tesseract-ocr-ind');
        }
    }
}
