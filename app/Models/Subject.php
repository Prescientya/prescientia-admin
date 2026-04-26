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
     * based on real teaching schedules (teacher_schedules),
     * counting distinct classes per subject.
     */
    public static function loadJumlahKelas($subjects): void
    {
        if ($subjects->isEmpty()) return;

        $ids = $subjects->pluck('id');

        // Count distinct class_id from actual schedules only.
        // This avoids inflated counts from broad assignment rules in subject_classes.
        $counts = DB::table('teacher_schedules as ts')
            ->join('teacher_subject as tsub', function ($join) {
                $join->on('ts.teacher_id', '=', 'tsub.teacher_id')
                     ->on('ts.subject_id', '=', 'tsub.subject_id');
            })
            ->whereIn('ts.subject_id', $ids)
            ->groupBy('ts.subject_id')
            ->select('ts.subject_id', DB::raw('COUNT(DISTINCT ts.class_id) as jumlah'))
            ->pluck('jumlah', 'ts.subject_id');

        foreach ($subjects as $subject) {
            $subject->jumlah_kelas = (int) $counts->get($subject->id, 0);
        }
    }
}
