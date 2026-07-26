<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EncryptionController extends Controller
{
    // ===================== Bcrypt =====================

    public function bcryptForm()
    {
        return view('tools.encrypt.bcrypt');
    }

    public function bcryptProcess(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:hash,verify'],
            'text' => ['required', 'string', 'max:5000'],
            'hash' => ['nullable', 'string', 'max:255', 'required_if:action,verify'],
        ], [
            'text.required' => 'Teks tidak boleh kosong.',
            'hash.required_if' => 'Masukkan hash bcrypt yang mau diverifikasi.',
        ]);

        if ($request->action === 'hash') {
            return back()
                ->withInput()
                ->with('action', 'hash')
                ->with('result', Hash::make($request->text));
        }

        $isValid = Hash::check($request->text, $request->hash);

        return back()
            ->withInput()
            ->with('action', 'verify')
            ->with('verify_result', $isValid);
    }

    // ===================== Base64 =====================

    public function base64Form()
    {
        return view('tools.encrypt.base64');
    }

    public function base64Process(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:encode,decode'],
            'text' => ['required', 'string', 'max:20000'],
        ], [
            'text.required' => 'Teks tidak boleh kosong.',
        ]);

        if ($request->action === 'encode') {
            $result = base64_encode($request->text);
        } else {
            $decoded = base64_decode($request->text, true);

            if ($decoded === false) {
                return back()->withInput()->with('error', 'Teks bukan format Base64 yang valid.');
            }

            $result = $decoded;
        }

        return back()->withInput()->with('action', $request->action)->with('result', $result);
    }

    // ===================== SHA256 =====================

    public function sha256Form()
    {
        return view('tools.encrypt.sha256');
    }

    public function sha256Process(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ], [
            'text.required' => 'Teks tidak boleh kosong.',
        ]);

        return back()->withInput()->with('result', hash('sha256', $request->text));
    }

    // ===================== MD5 =====================

    public function md5Form()
    {
        return view('tools.encrypt.md5');
    }

    public function md5Process(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ], [
            'text.required' => 'Teks tidak boleh kosong.',
        ]);

        return back()->withInput()->with('result', md5($request->text));
    }
}
