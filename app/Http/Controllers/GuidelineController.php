<?php

namespace App\Http\Controllers;

use App\Models\GuidelinePage;
use App\Models\GuidelineSection;
use App\Models\GuidelineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuidelineController extends Controller
{
    /* ── INDEX ─────────────────────────────────────────────── */

    public function index()
    {
        foreach (['siswa', 'guru'] as $type) {
            GuidelinePage::firstOrCreate(
                ['user_type' => $type],
                [
                    'title'        => 'Panduan Penggunaan Aplikasi ' . ucfirst($type) . ' Prescientia',
                    'is_published' => false,
                ]
            );
        }

        $pages = GuidelinePage::withCount('sections')->get()->keyBy('user_type');

        return view('Guidelines.index', compact('pages'));
    }

    /* ── SHOW (manage sections & items) ────────────────────── */

    public function show(GuidelinePage $guideline)
    {
        $guideline->load(['sections.items']);

        return view('Guidelines.show', compact('guideline'));
    }

    /* ── UPDATE PAGE (title, subtitle) ─────────────────────── */

    public function updatePage(Request $request, GuidelinePage $guideline)
    {
        $request->validate([
            'title'    => 'required|string|max:20',
            'subtitle' => 'nullable|string|max:1000',
        ], [
            'title.required' => 'Judul halaman wajib diisi.',
            'title.max'      => 'Judul maksimal 20 karakter.',
        ]);

        $guideline->update([
            'title'    => $request->title,
            'subtitle' => $request->subtitle,
        ]);

        return back()->with('success', 'Informasi halaman panduan berhasil diperbarui.');
    }

    /* ── TOGGLE PUBLISH ─────────────────────────────────────── */

    public function togglePublish(GuidelinePage $guideline)
    {
        $guideline->update(['is_published' => ! $guideline->is_published]);

        $status = $guideline->is_published ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Halaman panduan {$guideline->user_type_label} berhasil {$status}.");
    }

    /* ── STORE SECTION ──────────────────────────────────────── */

    public function storeSection(Request $request, GuidelinePage $guideline)
    {
        $request->validate([
            'title'       => 'required|string|max:20',
            'description' => 'nullable|string|max:2000',
            'order'       => 'nullable|integer|min:0|max:999',
        ], [
            'title.required' => 'Judul seksi wajib diisi.',
            'title.max'      => 'Judul seksi maksimal 20 karakter.',
        ]);

        $maxOrder = $guideline->sections()->max('order') ?? -1;

        $guideline->sections()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'order'       => $request->filled('order') ? (int) $request->order : $maxOrder + 1,
        ]);

        return back()->with('success', "Seksi \"{$request->title}\" berhasil ditambahkan.");
    }

    /* ── UPDATE SECTION ─────────────────────────────────────── */

    public function updateSection(Request $request, GuidelineSection $section)
    {
        $request->validate([
            'title'       => 'required|string|max:20',
            'description' => 'nullable|string|max:2000',
            'order'       => 'nullable|integer|min:0|max:999',
        ], [
            'title.required' => 'Judul seksi wajib diisi.',
            'title.max'      => 'Judul seksi maksimal 20 karakter.',
        ]);

        $section->update([
            'title'       => $request->title,
            'description' => $request->description,
            'order'       => $request->filled('order') ? (int) $request->order : $section->order,
        ]);

        return back()->with('success', "Seksi \"{$section->title}\" berhasil diperbarui.");
    }

    /* ── DESTROY SECTION ────────────────────────────────────── */

    public function destroySection(GuidelineSection $section)
    {
        // Delete all item images first
        foreach ($section->items as $item) {
            if ($item->image_path) {
                Storage::delete($item->image_path);
            }
        }

        $title = $section->title;
        $section->delete();

        return back()->with('success', "Seksi \"{$title}\" berhasil dihapus.");
    }

    /* ── STORE ITEM ─────────────────────────────────────────── */

    public function storeItem(Request $request, GuidelineSection $section)
    {
        $request->validate([
            'title'   => 'required|string|max:20',
            'content' => 'nullable|string|max:5000',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'order'   => 'nullable|integer|min:0|max:999',
        ], [
            'title.required'  => 'Judul langkah wajib diisi.',
            'title.max'       => 'Judul langkah maksimal 20 karakter.',
            'image.image'     => 'File harus berupa gambar.',
            'image.mimes'     => 'Format gambar: jpg, jpeg, png, atau webp.',
            'image.max'       => 'Ukuran gambar maksimal 3 MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $userType  = $section->page->user_type;
            $imagePath = $request->file('image')->store("guidelines/{$userType}", 'public');
        }

        $maxOrder = $section->items()->max('order') ?? -1;

        $section->items()->create([
            'title'      => $request->title,
            'content'    => $request->content,
            'image_path' => $imagePath,
            'order'      => $request->filled('order') ? (int) $request->order : $maxOrder + 1,
        ]);

        return back()->with('success', "Langkah \"{$request->title}\" berhasil ditambahkan.");
    }

    /* ── UPDATE ITEM ────────────────────────────────────────── */

    public function updateItem(Request $request, GuidelineItem $item)
    {
        $request->validate([
            'title'         => 'required|string|max:20',
            'content'       => 'nullable|string|max:5000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'remove_image'  => 'nullable|boolean',
            'order'         => 'nullable|integer|min:0|max:999',
        ], [
            'title.required' => 'Judul langkah wajib diisi.',
            'title.max'      => 'Judul langkah maksimal 20 karakter.',
            'image.image'    => 'File harus berupa gambar.',
            'image.mimes'    => 'Format gambar: jpg, jpeg, png, atau webp.',
            'image.max'      => 'Ukuran gambar maksimal 3 MB.',
        ]);

        $imagePath = $item->image_path;

        // Remove image jika diminta atau ada gambar baru
        if ($request->boolean('remove_image') || $request->hasFile('image')) {
            if ($imagePath) {
                Storage::delete($imagePath);
                $imagePath = null;
            }
        }

        // Upload gambar baru jika ada
        if ($request->hasFile('image')) {
            $userType  = $item->section->page->user_type;
            $imagePath = $request->file('image')->store("guidelines/{$userType}", 'public');
        }

        $item->update([
            'title'      => $request->title,
            'content'    => $request->content,
            'image_path' => $imagePath,
            'order'      => $request->filled('order') ? (int) $request->order : $item->order,
        ]);

        return back()->with('success', "Langkah \"{$item->title}\" berhasil diperbarui.");
    }

    /* ── DESTROY ITEM ───────────────────────────────────────── */

    public function destroyItem(GuidelineItem $item)
    {
        if ($item->image_path) {
            Storage::delete($item->image_path);
        }

        $title = $item->title;
        $item->delete();

        return back()->with('success', "Langkah \"{$title}\" berhasil dihapus.");
    }
}
