<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Throwable;

class PdfMergeController extends Controller
{
    public function create()
    {
        return view('tools.merge-pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdfs' => ['required', 'array', 'min:2', 'max:20'],
            'pdfs.*' => ['file', 'mimes:pdf', 'max:20480'],
        ], [
            'pdfs.required' => 'Pilih minimal 2 file PDF untuk digabung.',
            'pdfs.min' => 'Pilih minimal 2 file PDF untuk digabung.',
            'pdfs.*.mimes' => 'Semua file harus berformat PDF.',
            'pdfs.*.max' => 'Ukuran tiap PDF maksimal 20MB.',
        ]);

        try {
            $pdf = new Fpdi;
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            foreach ($request->file('pdfs') as $file) {
                $pageCount = $pdf->setSourceFile($file->getRealPath());

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

            return back()
                ->with('success', 'PDF berhasil digabung menjadi 1 file!')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-merged.pdf');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menggabung PDF: '.$e->getMessage());
        }
    }
}
