<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckOnboardingIdentityRequest extends FormRequest
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
            'email'             => ['nullable', 'email', 'required_without_all:cpf,cnpj'],
            'document_type'     => ['nullable', 'in:cpf,cnpj', 'required_without:email'],
            'cpf'               => ['nullable', 'string', 'size:11', 'required_if:document_type,cpf'],
            'cnpj'              => ['nullable', 'string', 'size:14', 'required_if:document_type,cnpj'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $cpf = $this->input('cpf');
        $cnpj = $this->input('cnpj');

        $this->merge([
            'email' => is_string($email) ? mb_strtolower(trim($email)) : $email,
            'document_type' => is_string($this->input('document_type'))
                ? trim((string) $this->input('document_type'))
                : $this->input('document_type'),
            'cpf' => is_string($cpf) ? preg_replace('/\D+/', '', $cpf) : $cpf,
            'cnpj' => is_string($cnpj) ? preg_replace('/\D+/', '', $cnpj) : $cnpj,
        ]);
    }
}
