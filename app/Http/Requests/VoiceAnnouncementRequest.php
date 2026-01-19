<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoiceAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:50'],
            'counter' => ['required', 'integer', 'min:1', 'max:999'],
            'service_name' => ['required', 'string', 'max:255'],
            'called_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number.required' => 'Nomor antrian wajib diisi.',
            'counter.required' => 'Nomor loket wajib diisi.',
            'service_name.required' => 'Nama layanan wajib diisi.',
            'called_at.required' => 'Waktu panggilan wajib diisi.',
        ];
    }
}
