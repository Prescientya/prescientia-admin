<?php

namespace App\Http\Requests;

use App\Models\ClassPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming middleware handles auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classPeriodId = $this->route('class_period') ? $this->route('class_period')->id : null;
        
        return [
            'day' => ['required', 'string', Rule::in(['senin', 'selasa', 'rabu', 'kamis', 'jumat'])],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('class_periods')->where(function ($query) {
                    return $query->where('day', $this->day);
                })->ignore($classPeriodId)
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('class_periods')->where(function ($query) {
                    return $query->where('day', $this->day);
                })->ignore($classPeriodId)
            ],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'activity_type' => ['required', 'string', Rule::in(['belajar', 'istirahat', 'shalat'])],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasOverlap()) {
                $validator->errors()->add('start_time', 'Waktu jam pelajaran ini bertabrakan dengan jam pelajaran lain pada hari yang sama.');
            }
        });
    }

    /**
     * Check if the time period overlaps with existing periods on the same day.
     */
    protected function hasOverlap(): bool
    {
        $classPeriodId = $this->route('class_period') ? $this->route('class_period')->id : null;
        
        $query = ClassPeriod::where('day', $this->day)
            ->where(function ($query) {
                // Check for any overlap
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>=', $this->end_time);
                    });
            });
            
        if ($classPeriodId) {
            $query->where('id', '!=', $classPeriodId);
        }
        
        return $query->exists();
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'day.required' => 'Hari harus diisi.',
            'day.in' => 'Hari harus salah satu dari: senin, selasa, rabu, kamis, jumat.',
            'sequence.required' => 'Urutan jam harus diisi.',
            'sequence.unique' => 'Urutan jam ini sudah ada untuk hari yang dipilih.',
            'start_time.required' => 'Waktu mulai harus diisi.',
            'start_time.unique' => 'Waktu mulai ini sudah digunakan pada hari yang sama.',
            'end_time.required' => 'Waktu selesai harus diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'activity_type.required' => 'Jenis aktivitas harus diisi.',
        ];
    }
}
