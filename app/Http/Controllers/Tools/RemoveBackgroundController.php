<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use App\Models\ProcessedImage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RemoveBackgroundController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const MAX_SIZE_KB = 8 * 1024; // 8 MB
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Tampilkan form upload tool Remove Background.
     */
    public function create(): View
    {
        $recent = ProcessedImage::query()
            ->where('tool', 'remove-background')
            ->where('status', 'done')
            ->latest()
            ->limit(6)
            ->get();

        return view('tools.remove-background', [
            'recent' => $recent,
            'eligibleFiles' => $this->eligibleMemberFiles(self::IMAGE_EXTENSIONS),
        ]);
    }

    /**
     * Proses foto (upload baru atau dari Member Area), kirim ke microservice rembg, simpan hasilnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required_without:member_file_id', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_SIZE_KB],
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
            $originalName = $memberFile->original_name;
            $originalPath = 'uploads/'.Str::uuid().'.'.$memberFile->extension;
            Storage::disk('public')->put($originalPath, file_get_contents($sourceAbsolutePath));
            $originalSize = $memberFile->size;
        } else {
            $file = $request->file('photo');
            $originalPath = $file->store('uploads', 'public');
            $originalName = $file->getClientOriginalName();
            $originalSize = $file->getSize();
        }

        $record = ProcessedImage::create([
            'tool' => 'remove-background',
            'original_path' => $originalPath,
            'original_name' => $originalName,
            'original_size' => $originalSize,
            'status' => 'processing',
            'ip_address' => $request->ip(),
        ]);

        try {
            $resultPath = $this->callRembgService($originalPath);

            $record->update([
                'result_path' => $resultPath,
                'status' => 'done',
            ]);

            $this->saveResultToMemberHasil(
                Storage::disk('public')->path($resultPath),
                pathinfo($originalName, PATHINFO_FILENAME).'-no-bg.png',
                'png',
                'image/png'
            );

            return redirect()
                ->route('tools.remove-background')
                ->with('success', 'Background berhasil dihapus!')
                ->with('result', $record->fresh());
        } catch (GuzzleException $e) {
            $record->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tools.remove-background')
                ->with('error', 'Gagal memproses gambar. Pastikan service remove-background (Python/rembg) sedang berjalan.');
        }
    }

    /**
     * Kirim gambar ke microservice Python (FastAPI + rembg) untuk dihapus background-nya.
     *
     * @throws GuzzleException
     */
    private function callRembgService(string $originalPath): string
    {
        $client = new Client([
            'base_uri' => config('services.rembg.url'),
            'timeout' => config('services.rembg.timeout', 60),
        ]);

        $absolutePath = Storage::disk('public')->path($originalPath);

        $response = $client->post('/remove-bg', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($absolutePath, 'r'),
                    'filename' => basename($absolutePath),
                ],
            ],
        ]);

        $resultFilename = 'results/'.Str::uuid().'.png';

        Storage::disk('public')->put($resultFilename, $response->getBody()->getContents());

        return $resultFilename;
    }

    public function destroy(ProcessedImage $processedImage): RedirectResponse
    {
        Storage::disk('public')->delete(array_filter([
            $processedImage->original_path,
            $processedImage->result_path,
        ]));

        $processedImage->delete();

        return redirect()
            ->route('tools.remove-background')
            ->with('success', 'Gambar berhasil dihapus.');
    }
}
