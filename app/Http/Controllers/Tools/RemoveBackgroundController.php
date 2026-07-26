<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
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
    private const MAX_SIZE_KB = 8 * 1024; // 8 MB

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

        return view('tools.remove-background', ['recent' => $recent]);
    }

    /**
     * Proses upload foto, kirim ke microservice rembg, simpan hasilnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_SIZE_KB],
        ], [
            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image' => 'File yang diupload harus berupa gambar.',
            'photo.mimes' => 'Format yang didukung: JPG, JPEG, PNG, WEBP.',
            'photo.max' => 'Ukuran foto maksimal 8MB.',
        ]);

        $file = $request->file('photo');

        $originalPath = $file->store('uploads', 'public');

        $record = ProcessedImage::create([
            'tool' => 'remove-background',
            'original_path' => $originalPath,
            'original_name' => $file->getClientOriginalName(),
            'original_size' => $file->getSize(),
            'status' => 'processing',
            'ip_address' => $request->ip(),
        ]);

        try {
            $resultPath = $this->callRembgService($originalPath);

            $record->update([
                'result_path' => $resultPath,
                'status' => 'done',
            ]);

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
