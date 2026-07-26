<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PdfCompressController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const PDF_EXTENSION = ['pdf'];

    /** Preset kualitas Ghostscript: /screen (paling kecil), /ebook (seimbang), /printer (kualitas tinggi) */
    private const QUALITY_PRESETS = ['screen', 'ebook', 'printer'];

    public function create()
    {
        return view('tools.compress-pdf', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::PDF_EXTENSION),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf' => ['required_without:member_file_id', 'nullable', 'file', 'mimes:pdf', 'max:51200'],
            'member_file_id' => ['required_without:pdf', 'nullable', 'integer', 'exists:member_files,id'],
            'quality' => ['required', 'in:screen,ebook,printer'],
        ], [
            'pdf.required_without' => 'Pilih file PDF terlebih dahulu (upload atau dari Member Area).',
            'pdf.mimes' => 'File harus berformat PDF.',
            'pdf.max' => 'Ukuran PDF maksimal 50MB.',
        ]);

        if (! $this->ghostscriptAvailable()) {
            return back()->with('error', 'Fitur ini butuh Ghostscript terinstall di server. Jalankan: sudo apt install ghostscript');
        }

        if ($request->filled('member_file_id')) {
            $memberFile = $this->resolveMemberFile((int) $request->member_file_id, self::PDF_EXTENSION);
            $sourcePath = $this->memberFileAbsolutePath($memberFile);
            $originalName = $memberFile->original_name;
        } else {
            $sourcePath = $request->file('pdf')->getRealPath();
            $originalName = $request->file('pdf')->getClientOriginalName();
        }

        $originalSizeBytes = filesize($sourcePath);

        try {
            $filename = 'results/'.Str::uuid().'.pdf';
            Storage::disk('public')->makeDirectory('results');
            $outputPath = Storage::disk('public')->path($filename);

            $quality = $request->input('quality');

            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/%s -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
                escapeshellcmd($quality),
                escapeshellarg($outputPath),
                escapeshellarg($sourcePath)
            );

            exec($command, $outputLines, $returnCode);

            if ($returnCode !== 0 || ! file_exists($outputPath)) {
                return back()->with('error', 'Gagal memperkecil PDF: '.implode(' ', $outputLines));
            }

            $newSizeBytes = filesize($outputPath);
            $savedPercent = $originalSizeBytes > 0
                ? round((1 - ($newSizeBytes / $originalSizeBytes)) * 100)
                : 0;

            $this->saveResultToMemberHasil(
                $outputPath,
                pathinfo($originalName, PATHINFO_FILENAME).'-compressed.pdf',
                'pdf',
                'application/pdf'
            );

            return back()
                ->with('success', $savedPercent > 0
                    ? "PDF berhasil diperkecil {$savedPercent}%!"
                    : 'PDF berhasil diproses (ukuran sudah cukup optimal, tidak banyak berkurang).')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-compressed.pdf')
                ->with('original_size', $this->humanSize($originalSizeBytes))
                ->with('new_size', $this->humanSize($newSizeBytes));
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memperkecil PDF: '.$e->getMessage());
        }
    }

    private function ghostscriptAvailable(): bool
    {
        exec('which gs 2>/dev/null', $output, $returnCode);

        return $returnCode === 0 && ! empty($output);
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
