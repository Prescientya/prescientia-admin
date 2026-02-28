<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * External attribute — set by SubjectController::index() via a single
     * aggregate query instead of N+1 per-model accessor.
     */
    public $jumlah_kelas = 0;

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject');
    }

    public function subjectClasses()
    {
        return $this->hasMany(SubjectClass::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'subject_classes', 'subject_id', 'class_id')
                    ->withTimestamps();
    }

    /* ── Bulk class-count loader (call once in controller) ─────────── */

    /**
     * Efficiently compute jumlah_kelas for a collection of subjects
     * using only 2 aggregate queries instead of 2N.
     */
    public static function loadJumlahKelas($subjects): void
    {
        if ($subjects->isEmpty()) return;

        $ids = $subjects->pluck('id');

        // 1) From subject_classes pivot
        $fromPivot = DB::table('subject_classes')
            ->whereIn('subject_id', $ids)
            ->select('subject_id', 'class_id')
            ->get();

        // 2) From teacher_schedules
        $fromSchedules = DB::table('teacher_schedules')
            ->whereIn('subject_id', $ids)
            ->select('subject_id', 'class_id')
            ->get();

        // Merge & count unique class_id per subject
        $merged = $fromPivot->concat($fromSchedules)
            ->groupBy('subject_id')
            ->map(fn($rows) => $rows->pluck('class_id')->unique()->count());

        foreach ($subjects as $subject) {
            $subject->jumlah_kelas = $merged->get($subject->id, 0);
        }
    }
}
