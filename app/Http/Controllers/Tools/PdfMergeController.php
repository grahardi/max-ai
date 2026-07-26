<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Throwable;

class PdfMergeController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const PDF_EXTENSION = ['pdf'];

    public function create()
    {
        return view('tools.merge-pdf', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::PDF_EXTENSION),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdfs' => ['nullable', 'array', 'max:20'],
            'pdfs.*' => ['file', 'mimes:pdf', 'max:20480'],
            'member_file_ids' => ['nullable', 'array', 'max:20'],
            'member_file_ids.*' => ['integer', 'exists:member_files,id'],
        ], [
            'pdfs.*.mimes' => 'Semua file harus berformat PDF.',
            'pdfs.*.max' => 'Ukuran tiap PDF maksimal 20MB.',
        ]);

        try {
            $pdfPaths = [];

            foreach ($request->file('pdfs', []) as $file) {
                $pdfPaths[] = $file->getRealPath();
            }

            foreach ($request->input('member_file_ids', []) as $id) {
                $memberFile = $this->resolveMemberFile((int) $id, self::PDF_EXTENSION);
                $pdfPaths[] = $this->memberFileAbsolutePath($memberFile);
            }

            if (count($pdfPaths) < 2) {
                return back()->with('error', 'Pilih minimal 2 file PDF untuk digabung (upload atau dari Member Area).');
            }

            $pdf = new Fpdi;
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            foreach ($pdfPaths as $path) {
                $pageCount = $pdf->setSourceFile($path);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            }

            $filename = 'results/'.Str::uuid().'.pdf';
            Storage::disk('public')->makeDirectory('results');
            $pdf->Output(Storage::disk('public')->path($filename), 'F');

            $this->saveResultToMemberHasil(
                Storage::disk('public')->path($filename),
                'gabungan-pdf.pdf',
                'pdf',
                'application/pdf'
            );

            return back()
                ->with('success', 'PDF berhasil digabung menjadi 1 file!')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-merged.pdf');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menggabung PDF: '.$e->getMessage());
        }
    }
}
