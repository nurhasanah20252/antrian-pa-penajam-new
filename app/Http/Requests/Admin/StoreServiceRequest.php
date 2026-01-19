<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:20', 'unique:services,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'prefix' => ['required', 'string', 'max:5', 'unique:services,prefix'],
            'average_time' => ['required', 'integer', 'min:1', 'max:120'],
            'max_daily_queue' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['boolean'],
            'requires_documents' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode layanan wajib diisi.',
            'code.unique' => 'Kode layanan sudah digunakan.',
            'name.required' => 'Nama layanan wajib diisi.',
            'prefix.required' => 'Prefix antrian wajib diisi.',
            'prefix.unique' => 'Prefix sudah digunakan layanan lain.',
            'average_time.required' => 'Estimasi waktu layanan wajib diisi.',
            'average_time.min' => 'Estimasi waktu minimal 1 menit.',
            'max_daily_queue.required' => 'Kuota harian wajib diisi.',
            'max_daily_queue.min' => 'Kuota harian minimal 1.',
        ];
    }
}
