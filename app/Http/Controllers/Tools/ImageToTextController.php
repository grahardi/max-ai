<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

class ImageToTextController extends Controller
{
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

            return back()
                ->with('success', 'Teks berhasil diekstrak dari gambar!')
                ->with('extracted_text', trim($text))
                ->with('preview_url', asset('storage/'.$path));
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal membaca teks dari gambar. Pastikan Tesseract OCR sudah terinstall di server: sudo apt install tesseract-ocr tesseract-ocr-ind');
        }
    }
}
