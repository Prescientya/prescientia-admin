<?php

namespace App\Services;

use App\Enums\StudentRole;
use App\Models\Student;
use App\Models\StudentClassRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentRoleService
{
    /**
     * Assign default role `pelajar` to the student.
     * If student has a class_id, create role in that class.
     * If student has no class_id, still create the role (will be updated when assigned to a class).
     */
    public function assignDefaultRole(Student $student): StudentClassRole
    {
        $role = StudentRole::PELAJAR;
        $classId = $student->class_id ?? 0; // Use 0 if no class assigned yet

        return StudentClassRole::firstOrCreate([
            'student_id' => $student->id,
            'class_id' => $classId,
        ], [
            'role' => $role,
        ]);
    }

    /**
     * Update a student's role within their current class, enforcing quotas and constraints.
     *
     * @throws ValidationException
     */
    public function updateRole(Student $student, string $newRole): StudentClassRole
    {
        if (!StudentRole::isValid($newRole)) {
            throw ValidationException::withMessages(['role' => 'Invalid role.']);
        }

        if (empty($student->class_id)) {
            throw ValidationException::withMessages(['class_id' => 'Student must belong to a class to change role.']);
        }

        $classId = $student->class_id;

        // Ensure student does not already have roles in other classes
        $other = StudentClassRole::where('student_id', $student->id)
            ->where('class_id', '<>', $classId)
            ->exists();

        if ($other) {
            throw ValidationException::withMessages(['student' => 'Student already has a role in another class.']);
        }

        // Quota enforcement
        $quota = StudentRole::quotas()[$newRole] ?? null;

        if (!is_null($quota)) {
            $count = StudentClassRole::where('class_id', $classId)
                ->where('role', $newRole)
                ->where('student_id', '<>', $student->id)
                ->count();

            if ($count >= $quota) {
                throw ValidationException::withMessages(['role' => "Role '{$newRole}' quota exceeded for this class."]);
            }
        }

        return DB::transaction(function () use ($student, $classId, $newRole) {
            /** @var StudentClassRole $record */
            $record = StudentClassRole::firstOrNew([
                'student_id' => $student->id,
                'class_id' => $classId,
            ]);

            $record->role = $newRole;
            $record->save();

            return $record;
        });
    }

    /**
     * Check whether a role can be assigned to a class (returns true if within quota).
     */
    public function canAssignRole(int $classId, string $role, ?int $excludeStudentId = null): bool
    {
        if (!StudentRole::isValid($role)) {
            return false;
        }

        $quota = StudentRole::quotas()[$role] ?? null;
        if (is_null($quota)) {
            return true;
        }

        $query = StudentClassRole::where('class_id', $classId)->where('role', $role);
        if ($excludeStudentId) {
            $query->where('student_id', '<>', $excludeStudentId);
        }

        return $query->count() < $quota;
    }
}
