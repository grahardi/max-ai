<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Throwable;
use ZipArchive;

class PdfSplitController extends Controller
{
    use SavesToMemberHasil;

    public function create()
    {
        return view('tools.split-pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'pdf.required' => 'Pilih file PDF terlebih dahulu.',
            'pdf.mimes' => 'File harus berformat PDF.',
            'pdf.max' => 'Ukuran PDF maksimal 20MB.',
        ]);

        $sourcePath = $request->file('pdf')->getRealPath();
        $tempDir = storage_path('app/tmp/'.Str::uuid());

        try {
            mkdir($tempDir, 0755, true);

            $probe = new Fpdi;
            $pageCount = $probe->setSourceFile($sourcePath);

            if ($pageCount < 2) {
                return back()->with('error', 'PDF hanya punya 1 halaman, tidak perlu dipecah.');
            }

            $pageFiles = [];

            for ($i = 1; $i <= $pageCount; $i++) {
                $single = new Fpdi;
                $single->setPrintHeader(false);
                $single->setPrintFooter(false);
                $single->setSourceFile($sourcePath);

                $templateId = $single->importPage($i);
                $size = $single->getTemplateSize($templateId);
                $single->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $single->useTemplate($templateId);

                $pagePath = $tempDir.'/halaman-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.pdf';
                $single->Output($pagePath, 'F');
                $pageFiles[] = $pagePath;
            }

            $zipFilename = 'results/'.Str::uuid().'.zip';
            Storage::disk('public')->makeDirectory('results');
            $zipFullPath = Storage::disk('public')->path($zipFilename);

            $zip = new ZipArchive;
            $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($pageFiles as $path) {
                $zip->addFile($path, basename($path));
            }
            $zip->close();

            $this->saveResultToMemberHasil(
                $zipFullPath,
                'pecahan-pdf.zip',
                'zip',
                'application/zip'
            );

            return back()
                ->with('success', "PDF berhasil dipecah menjadi {$pageCount} halaman!")
                ->with('download_url', asset('storage/'.$zipFilename))
                ->with('download_name', 'max-ai-split-pages.zip');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memecah PDF: '.$e->getMessage());
        } finally {
            if (is_dir($tempDir)) {
                array_map('unlink', glob($tempDir.'/*') ?: []);
                @rmdir($tempDir);
            }
        }
    }
}
