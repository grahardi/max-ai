<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCPDF;
use Throwable;

class ImageToPdfController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const PX_TO_MM = 0.264583; // konversi px (96dpi) ke mm
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function create()
    {
        return view('tools.image-to-pdf', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::IMAGE_EXTENSIONS),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos' => ['required_without:member_file_ids', 'nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'member_file_ids' => ['required_without:photos', 'nullable', 'array', 'max:20'],
            'member_file_ids.*' => ['integer', 'exists:member_files,id'],
        ], [
            'photos.required_without' => 'Pilih minimal 1 foto (upload atau dari Member Area).',
            'photos.*.image' => 'Semua file harus berupa gambar.',
            'photos.*.mimes' => 'Format yang didukung: JPG, JPEG, PNG, WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 8MB.',
        ]);

        try {
            // Kumpulkan path absolut semua gambar sumber, urut sesuai input.
            $imagePaths = [];

            foreach ($request->file('photos', []) as $photo) {
                $imagePaths[] = $photo->getRealPath();
            }

            foreach ($request->input('member_file_ids', []) as $id) {
                $memberFile = $this->resolveMemberFile((int) $id, self::IMAGE_EXTENSIONS);
                $imagePaths[] = $this->memberFileAbsolutePath($memberFile);
            }

            if (empty($imagePaths)) {
                return back()->with('error', 'Pilih minimal 1 foto.');
            }

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);

            foreach ($imagePaths as $path) {
                [$width, $height] = getimagesize($path);
                $mmWidth = $width * self::PX_TO_MM;
                $mmHeight = $height * self::PX_TO_MM;
                $orientation = $mmWidth > $mmHeight ? 'L' : 'P';

                $pdf->AddPage($orientation, [$mmWidth, $mmHeight]);
                $pdf->Image($path, 0, 0, $mmWidth, $mmHeight);
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
