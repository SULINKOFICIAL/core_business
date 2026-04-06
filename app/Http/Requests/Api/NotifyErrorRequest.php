<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class NotifyErrorRequest extends FormRequest
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
            'url' => ['nullable', 'string', 'max:1000'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'message' => ['required', 'string', 'max:5000'],
            'stack_trace' => ['nullable', 'string'],
            'status_code' => ['nullable', 'integer'],
            'system_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'array'],
        ];
    }
}
