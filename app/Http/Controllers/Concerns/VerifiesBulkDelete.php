<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait VerifiesBulkDelete
{
    protected function verifyBulkDelete(Request $request, string $expectedKeyword): void
    {
        $request->validate([
            'bulk_delete_password' => ['required', 'string'],
            'bulk_delete_confirmation' => ['required', 'string'],
        ], [
            'bulk_delete_password.required' => 'Password admin wajib diisi.',
            'bulk_delete_confirmation.required' => 'Ketik konfirmasi hapus untuk melanjutkan.',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin || !Hash::check((string) $request->input('bulk_delete_password'), $admin->password)) {
            throw ValidationException::withMessages([
                'bulk_delete_password' => 'Password admin salah.',
            ]);
        }

        $confirmation = preg_replace('/\s+/', ' ', strtoupper(trim((string) $request->input('bulk_delete_confirmation'))));
        $expected     = preg_replace('/\s+/', ' ', strtoupper(trim($expectedKeyword)));

        if ($confirmation !== $expected) {
            throw ValidationException::withMessages([
                'bulk_delete_confirmation' => 'Ketik "' . $expectedKeyword . '" dengan benar untuk melanjutkan.',
            ]);
        }
    }
}