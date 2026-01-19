<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'size:16'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_priority' => ['nullable', 'boolean'],
            'source' => ['nullable', 'string', 'in:online,kiosk'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_sms' => ['nullable', 'boolean'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan harus dipilih.',
            'service_id.exists' => 'Layanan tidak ditemukan.',
            'name.required' => 'Nama harus diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'nik.size' => 'NIK harus 16 digit.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'documents.array' => 'Dokumen harus berupa daftar file.',
            'documents.max' => 'Maksimal unggah 5 dokumen.',
            'documents.*.file' => 'Dokumen harus berupa file yang valid.',
            'documents.*.mimes' => 'Format dokumen harus PDF, JPG, atau PNG.',
            'documents.*.max' => 'Ukuran dokumen maksimal 5MB.'
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $serviceId = $this->input('service_id');

            if (! $serviceId) {
                return;
            }

            $service = Service::find($serviceId);

            if (! $service) {
                return;
            }

            $queueService = app(QueueService::class);

            if (! $queueService->isServiceAcceptingQueue($service)) {
                $validator->errors()->add('service_id', 'Layanan tidak tersedia saat ini.');
            }
        });
    }
}
