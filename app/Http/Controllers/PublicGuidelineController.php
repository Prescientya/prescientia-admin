<?php

namespace App\Http\Controllers;

use App\Models\GuidelinePage;

class PublicGuidelineController extends Controller
{
    public function show(string $type)
    {
        if (! in_array($type, ['siswa', 'guru'])) {
            abort(404);
        }

        $page = GuidelinePage::where('user_type', $type)
            ->with(['sections' => function ($q) {
                $q->orderBy('order')->with(['items' => function ($q2) {
                    $q2->orderBy('order');
                }]);
            }])
            ->first();

        $isPublished = $page && $page->is_published;

        return view('public.panduan', compact('page', 'type', 'isPublished'));
    }
}
