<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use App\Models\MemberFolder;
use App\Models\ProcessedImage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RemoveBackgroundController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const MAX_SIZE_KB = 8 * 1024; // 8 MB
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Tampilkan form upload tool Remove Background.
     * Riwayat hanya ditampilkan untuk member yang login (milik sendiri), guest tidak melihat riwayat sama sekali.
     */
    public function create(): View
    {
        $recent = collect();

        if (Auth::check()) {
            $hasilFolder = MemberFolder::where('user_id', Auth::id())
                ->where('is_system', true)
                ->where('name', 'Hasil')
                ->first();

            if ($hasilFolder) {
                $recent = $hasilFolder->files()
                    ->where('extension', 'png')
                    ->latest()
                    ->limit(6)
                    ->get();
            }
        }

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

            $request->session()->put('remove_bg_last_id', $record->id);

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
     * Ganti background hasil remove-bg dengan warna solid atau gambar sendiri.
     * Selalu meng-komposit dari file transparan asli (result_path), tidak pernah menimpanya,
     * supaya bisa ganti warna berkali-kali tanpa background sebelumnya "menempel".
     */
    public function applyBackground(Request $request): RedirectResponse
    {
        $request->validate([
            'processed_image_id' => ['required', 'integer', 'exists:processed_images,id'],
            'color' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'bg_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $lastId = (int) $request->session()->get('remove_bg_last_id');

        abort_unless($lastId > 0 && $lastId === (int) $request->processed_image_id, 403);

        $record = ProcessedImage::findOrFail($request->processed_image_id);
        abort_unless($record->status === 'done' && $record->result_path, 404);

        try {
            $foregroundPath = Storage::disk('public')->path($record->result_path);
            $foreground = @imagecreatefrompng($foregroundPath);

            if ($foreground === false) {
                throw new RuntimeException('Gagal membaca gambar hasil remove background.');
            }

            imagesavealpha($foreground, true);
            imagealphablending($foreground, true);

            $width = imagesx($foreground);
            $height = imagesy($foreground);

            $canvas = imagecreatetruecolor($width, $height);

            if ($request->hasFile('bg_image')) {
                $this->paintCoverBackground(
                    $canvas,
                    $request->file('bg_image')->getRealPath(),
                    $request->file('bg_image')->getMimeType(),
                    $width,
                    $height
                );
            } else {
                $hex = ltrim($request->input('color') ?: 'ffffff', '#');
                if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
                    $hex = 'ffffff';
                }
                [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
                $bgColor = imagecolorallocate($canvas, $r, $g, $b);
                imagefilledrectangle($canvas, 0, 0, $width, $height, $bgColor);
            }

            // Tempel foreground (transparan) di atas canvas berwarna, alpha diblend otomatis oleh GD.
            imagealphablending($canvas, true);
            imagecopy($canvas, $foreground, 0, 0, 0, 0, $width, $height);

            $filename = 'results/'.Str::uuid().'.png';
            Storage::disk('public')->makeDirectory('results');
            $outputPath = Storage::disk('public')->path($filename);
            imagepng($canvas, $outputPath);

            $this->saveResultToMemberHasil(
                $outputPath,
                pathinfo($record->original_name, PATHINFO_FILENAME).'-background-baru.png',
                'png',
                'image/png'
            );

            return redirect()
                ->route('tools.remove-background')
                ->with('success', 'Background berhasil diganti!')
                ->with('result', $record)
                ->with('composited_url', asset('storage/'.$filename));
        } catch (Throwable $e) {
            return redirect()
                ->route('tools.remove-background')
                ->with('error', 'Gagal mengganti background: '.$e->getMessage())
                ->with('result', $record);
        }
    }

    /**
     * Isi $canvas dengan gambar background yang di-cover-fit (scale + crop tengah)
     * supaya menutupi seluruh area $targetWidth x $targetHeight.
     */
    private function paintCoverBackground($canvas, string $imagePath, ?string $mimeType, int $targetWidth, int $targetHeight): void
    {
        $source = match ($mimeType) {
            'image/png' => @imagecreatefrompng($imagePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($imagePath) : false,
            default => @imagecreatefromjpeg($imagePath),
        };

        if ($source === false || $source === null) {
            throw new RuntimeException('Gagal membaca gambar background yang diupload.');
        }

        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);

        $scale = max($targetWidth / $srcWidth, $targetHeight / $srcHeight);
        $scaledWidth = max(1, (int) ceil($srcWidth * $scale));
        $scaledHeight = max(1, (int) ceil($srcHeight * $scale));

        $resized = imagecreatetruecolor($scaledWidth, $scaledHeight);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $srcWidth, $srcHeight);

        $offsetX = (int) (($scaledWidth - $targetWidth) / 2);
        $offsetY = (int) (($scaledHeight - $targetHeight) / 2);

        imagecopy($canvas, $resized, 0, 0, $offsetX, $offsetY, $targetWidth, $targetHeight);
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
