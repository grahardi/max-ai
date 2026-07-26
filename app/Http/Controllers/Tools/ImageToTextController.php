<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

class ImageToTextController extends Controller
{
    use SavesToMemberHasil;

    public function create()
    {
        return view('tools.image-to-text');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image' => 'File yang diupload harus berupa gambar.',
            'photo.mimes' => 'Format yang didukung: JPG, JPEG, PNG, WEBP.',
            'photo.max' => 'Ukuran foto maksimal 8MB.',
        ]);

        $path = $request->file('photo')->store('uploads', 'public');
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
