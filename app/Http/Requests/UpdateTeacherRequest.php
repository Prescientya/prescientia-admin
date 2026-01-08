<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $teacherId = $this->route('id');

        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->getTeacher()->user_id),
            ],
            'nip' => [
                'required',
                Rule::unique('teachers', 'nip')->ignore($teacherId),
            ],
            'name' => 'required|string|max:100',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'role' => 'required|in:Pengajar,Walikelas',
            'phone_number' => 'nullable|string|max:20',
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'nip.required' => 'NIP harus diisi',
            'nip.unique' => 'NIP sudah terdaftar',
            'name.required' => 'Nama lengkap harus diisi',
            'name.max' => 'Nama maksimal 100 karakter',
            'gender.required' => 'Jenis kelamin harus dipilih',
            'date_of_birth.required' => 'Tanggal lahir harus diisi',
            'date_of_birth.date' => 'Format tanggal tidak valid',
            'role.required' => 'Role harus dipilih',
            'phone_number.max' => 'Nomor telepon maksimal 20 karakter',
            'departments.*.max' => 'Bidang studi maksimal 100 karakter',
            'photo_profile.image' => 'File harus berupa gambar',
            'photo_profile.mimes' => 'Format gambar hanya JPG, PNG, atau JPEG',
            'photo_profile.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }

    /**
     * Get the teacher instance.
     */
    protected function getTeacher()
    {
        return \App\Models\Teacher::findOrFail($this->route('id'));
    }
}
