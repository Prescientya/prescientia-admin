<?php

namespace App\Http\Controllers;

/**
 * Menyajikan halaman Kebijakan Privasi publik untuk app siswa & guru.
 *
 * Halaman ini WAJIB dapat diakses tanpa login dari mana saja (syarat Google
 * Play). Kontennya statis (di-hardcode pada blade) sehingga tidak dapat diedit
 * lewat UI mana pun — memenuhi syarat Play "privacy policy must be not editable".
 */
class PrivacyPolicyController extends Controller
{
    public function show(string $type)
    {
        abort_unless(in_array($type, ['siswa', 'guru'], true), 404);

        return view('public.privacy_policy', ['type' => $type]);
    }
}
