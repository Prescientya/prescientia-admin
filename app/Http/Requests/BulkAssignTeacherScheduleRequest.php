<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAssignTeacherScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'day' => ['required', 'string', Rule::in(['senin', 'selasa', 'rabu', 'kamis', 'jumat'])],
            'semester' => ['required', 'integer', Rule::in([1, 2])],
            'period_ids' => ['required', 'array', 'min:1'],
            'period_ids.*' => ['required', 'exists:class_periods,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Guru harus dipilih.',
            'class_id.required' => 'Kelas harus dipilih.',
            'subject_id.required' => 'Mata pelajaran harus dipilih.',
            'day.required' => 'Hari harus dipilih.',
            'semester.required' => 'Semester harus dipilih.',
            'period_ids.required' => 'Minimal pilih satu jam pelajaran.',
            'period_ids.min' => 'Minimal pilih satu jam pelajaran.',
        ];
    }
}
