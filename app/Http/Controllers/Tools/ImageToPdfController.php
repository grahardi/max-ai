<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCPDF;
use Throwable;

class ImageToPdfController extends Controller
{
    use SavesToMemberHasil;

    private const PX_TO_MM = 0.264583; // konversi px (96dpi) ke mm

    public function create()
    {
        return view('tools.image-to-pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'photos.required' => 'Pilih minimal 1 foto.',
            'photos.*.image' => 'Semua file harus berupa gambar.',
            'photos.*.mimes' => 'Format yang didukung: JPG, JPEG, PNG, WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 8MB.',
        ]);

        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);

            foreach ($request->file('photos') as $photo) {
                [$width, $height] = getimagesize($photo->getRealPath());
                $mmWidth = $width * self::PX_TO_MM;
                $mmHeight = $height * self::PX_TO_MM;
                $orientation = $mmWidth > $mmHeight ? 'L' : 'P';

                $pdf->AddPage($orientation, [$mmWidth, $mmHeight]);
                $pdf->Image($photo->getRealPath(), 0, 0, $mmWidth, $mmHeight);
            }

            $filename = 'results/'.Str::uuid().'.pdf';
            Storage::disk('public')->makeDirectory('results');
            $pdf->Output(Storage::disk('public')->path($filename), 'F');

            $this->saveResultToMemberHasil(
                Storage::disk('public')->path($filename),
                'gambar-ke-pdf.pdf',
                'pdf',
                'application/pdf'
            );

            return back()
                ->with('success', 'Foto berhasil dijadikan PDF!')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-image-to-pdf.pdf');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal membuat PDF dari foto: '.$e->getMessage());
        }
    }
}
