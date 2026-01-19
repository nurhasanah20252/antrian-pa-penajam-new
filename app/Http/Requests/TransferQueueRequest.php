<?php

namespace App\Http\Requests;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferQueueRequest extends FormRequest
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
            'target_service_id' => ['required', 'exists:services,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_service_id.required' => 'Layanan tujuan harus dipilih.',
            'target_service_id.exists' => 'Layanan tujuan tidak ditemukan.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $targetServiceId = $this->input('target_service_id');
            $queue = $this->route('queue');

            if (! $targetServiceId || ! $queue instanceof Queue) {
                return;
            }

            if ($queue->service_id === (int) $targetServiceId) {
                $validator->errors()->add('target_service_id', 'Tidak dapat transfer ke layanan yang sama.');

                return;
            }

            $targetService = Service::find($targetServiceId);

            if ($targetService && ! $targetService->is_active) {
                $validator->errors()->add('target_service_id', 'Layanan tujuan tidak aktif.');
            }
        });
    }
}
