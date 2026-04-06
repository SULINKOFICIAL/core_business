<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinalizeOnboardingRequest extends FormRequest
{
    /**
     * Possíveis objetivos
     */
    private const MAIN_GOALS = [
        'centralizar_atendimentos',
        'vender_online',
        'controlar_estoque',
        'vender_servicos',
    ];

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
            'step'                  => ['nullable', 'in:account,company,goal,address'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'document_type'         => ['required', 'in:cnpj,cpf'],
            'cpf'                   => ['nullable', 'string', 'size:11', 'required_if:document_type,cpf'],
            'cnpj'                  => ['nullable', 'string', 'size:14', 'required_if:document_type,cnpj'],
            'name'                  => ['nullable', 'string', 'max:255'],
            'company'               => ['nullable', 'string', 'max:255'],
            'whatsapp'              => ['nullable', 'string', 'max:20'],
            'password'              => ['nullable', 'string'],
            'main_goals'            => ['nullable', 'array'],
            'main_goals.*'          => ['string', Rule::in(self::MAIN_GOALS)],
            'company_profile'       => ['nullable', 'string', 'max:100'],
            'company_zip_code'      => ['nullable', 'string', 'size:8'],
            'company_city_state'    => ['nullable', 'string', 'max:255'],
            'company_address'       => ['nullable', 'string', 'max:255'],
            'company_neighborhood'  => ['nullable', 'string', 'max:255'],
            'company_number'        => ['nullable', 'string', 'max:50'],
            'company_complement'    => ['nullable', 'string', 'max:255'],
            'tips_whatsapp'         => ['nullable', 'boolean'],
            'tips_email'            => ['nullable', 'boolean'],
            'has_coupon'            => ['nullable', 'boolean'],
            'coupon_code'           => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email     = $this->input('email');
        $whatsapp  = $this->input('whatsapp');
        $cpf       = $this->input('cpf');
        $cnpj      = $this->input('cnpj');
        $zipCode   = $this->input('company_zip_code');
        $mainGoals = $this->input('main_goals');

        $this->merge([
            'email'             => is_string($email) ? mb_strtolower(trim($email)) : $email,
            'document_type'     => is_string($this->input('document_type'))
                ? trim((string) $this->input('document_type'))
                : $this->input('document_type'),
            'whatsapp'          => is_string($whatsapp) ? onlyNumbers($whatsapp) : $whatsapp,
            'cpf'               => is_string($cpf) ? onlyNumbers($cpf) : $cpf,
            'cnpj'              => is_string($cnpj) ? onlyNumbers($cnpj) : $cnpj,
            'company_zip_code'  => is_string($zipCode) ? onlyNumbers($zipCode) : $zipCode,
            'has_coupon'        => $this->boolean('has_coupon'),
            'tips_whatsapp'     => $this->boolean('tips_whatsapp'),
            'tips_email'        => $this->boolean('tips_email'),
            'main_goals'        => is_array($mainGoals)
                ? array_values(array_unique(array_filter($mainGoals, fn ($goal) => is_string($goal) && $goal !== '')))
                : $mainGoals,
        ]);
    }
}
