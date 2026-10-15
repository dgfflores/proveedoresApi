<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rfc_receptor' => strtoupper(trim((string) $this->input('rfc_receptor'))),
            'uuid'         => $this->filled('uuid') ? trim($this->input('uuid')) : null,
            'track_id'     => $this->filled('track_id') ? trim($this->input('track_id')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'rfc_receptor' => ['required', 'string', 'max:13'],
            'uuid'         => ['nullable', 'required_without:track_id', 'uuid'],
            'track_id'     => ['nullable', 'required_without:uuid', 'string', 'size:20'],
        ];
    }
}