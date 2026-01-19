<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id', 'unique:officers,user_id'],
            'service_id' => ['required', 'exists:services,id'],
            'counter_number' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
            'max_concurrent' => ['integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Pilih pengguna untuk petugas.',
            'user_id.exists' => 'Pengguna tidak ditemukan.',
            'user_id.unique' => 'Pengguna sudah terdaftar sebagai petugas.',
            'service_id.required' => 'Pilih layanan untuk petugas.',
            'service_id.exists' => 'Layanan tidak ditemukan.',
            'counter_number.required' => 'Nomor loket wajib diisi.',
            'counter_number.min' => 'Nomor loket minimal 1.',
            'max_concurrent.min' => 'Maksimal antrian bersamaan minimal 1.',
        ];
    }
}
