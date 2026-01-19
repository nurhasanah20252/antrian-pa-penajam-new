<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('call', $this->route('queue')) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
