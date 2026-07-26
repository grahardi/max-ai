<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\Concerns\SavesToMemberHasil;
use App\Http\Controllers\Tools\Concerns\UsesMemberFileSource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageEnhanceController extends Controller
{
    use SavesToMemberHasil, UsesMemberFileSource;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function create()
    {
        return view('tools.enhance-image', [
            'eligibleFiles' => $this->eligibleMemberFiles(self::IMAGE_EXTENSIONS),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required_without:member_file_id', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'member_file_id' => ['required_without:photo', 'nullable', 'integer', 'exists:member_files,id'],
            'scale' => ['required', 'in:1.5,2,3'],
        ], [
            'photo.required_without' => 'Pilih foto terlebih dahulu (upload atau dari Member Area).',
            'photo.max' => 'Ukuran foto maksimal 8MB.',
        ]);

        if ($request->filled('member_file_id')) {
            $memberFile = $this->resolveMemberFile((int) $request->member_file_id, self::IMAGE_EXTENSIONS);
            $sourcePath = $this->memberFileAbsolutePath($memberFile);
            $originalName = $memberFile->original_name;
            $mimeType = $memberFile->mime_type ?: 'image/jpeg';
            $sendFilename = 'file.'.$memberFile->extension;
        } else {
            $sourcePath = $request->file('photo')->getRealPath();
            $originalName = $request->file('photo')->getClientOriginalName();
            $mimeType = $request->file('photo')->getMimeType();
            $sendFilename = 'file.'.strtolower($request->file('photo')->getClientOriginalExtension());
        }

        try {
            $client = new Client([
                'base_uri' => config('services.rembg.url'),
                'timeout' => config('services.rembg.timeout', 60),
            ]);

            $response = $client->post('/enhance-image', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($sourcePath, 'r'),
                        'filename' => $sendFilename,
                        'headers' => ['Content-Type' => $mimeType],
                    ],
                    [
                        'name' => 'scale',
                        'contents' => $request->input('scale'),
                    ],
                ],
            ]);

            $filename = 'results/'.Str::uuid().'.png';
            Storage::disk('public')->makeDirectory('results');
            Storage::disk('public')->put($filename, $response->getBody()->getContents());

            $this->saveResultToMemberHasil(
                Storage::disk('public')->path($filename),
                pathinfo($originalName, PATHINFO_FILENAME).'-enhanced.png',
                'png',
                'image/png'
            );

            return back()
                ->with('success', 'Gambar berhasil diperjelas & diperbesar!')
                ->with('download_url', asset('storage/'.$filename))
                ->with('download_name', 'max-ai-enhanced.png')
                ->with('preview_url', asset('storage/'.$filename));
        } catch (GuzzleException $e) {
            $detail = $e->getMessage();
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $detail = (string) $e->getResponse()->getBody();
            }
            \Illuminate\Support\Facades\Log::error('enhance-image failed: '.$detail);

            return back()->with('error', 'Gagal memproses gambar. Pastikan service AI (Python) sedang berjalan.');
        }
    }
}
