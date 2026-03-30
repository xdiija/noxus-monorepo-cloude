<?php

namespace App\Http\Requests;

use App\Helpers\CnpjHelper;
use App\Helpers\PhoneHelper;
use App\Rules\CnpjRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierStoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj'    => CnpjHelper::sanitize($this->cnpj),
            'phone_1' => PhoneHelper::sanitize($this->phone_1),
            'phone_2' => PhoneHelper::sanitize($this->phone_2),
        ]);
    }

    public function rules(): array
    {
        $supplierId = $this->route('id');

        return [
            'nome_fantasia'      => ['required', 'string', 'min:3', 'max:255'],
            'razao_social'       => ['required', 'string', 'min:3', 'max:255'],
            'inscricao_estadual' => ['nullable', 'string', 'max:50'],
            'email'              => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],
            'cnpj'               => [
                'required',
                'max:18',
                Rule::unique('suppliers', 'cnpj')->ignore($supplierId),
                new CnpjRule(),
            ],
            'phone_1'            => ['nullable', new PhoneRule()],
            'phone_2'            => ['nullable', new PhoneRule()],
            'status'             => ['required', 'integer', Rule::in([1, 2])],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_fantasia.required' => 'O campo nome fantasia é obrigatório.',
            'nome_fantasia.min'      => 'O campo nome fantasia deve ter no mínimo 3 caracteres.',
            'nome_fantasia.max'      => 'O campo nome fantasia deve ter no máximo 255 caracteres.',

            'razao_social.required'  => 'A razão social é obrigatória.',
            'razao_social.min'       => 'A razão social deve ter no mínimo 3 caracteres.',
            'razao_social.max'       => 'A razão social deve ter no máximo 255 caracteres.',

            'inscricao_estadual.max' => 'A inscrição estadual deve ter no máximo 50 caracteres.',

            'email.required'         => 'O campo e-mail é obrigatório.',
            'email.email'            => 'O campo e-mail deve conter um endereço válido.',
            'email.max'              => 'O campo e-mail deve ter no máximo 255 caracteres.',
            'email.unique'           => 'O e-mail informado já está em uso.',

            'cnpj.required'          => 'O campo CNPJ é obrigatório.',
            'cnpj.max'               => 'O campo CNPJ deve ter no máximo 18 caracteres.',
            'cnpj.unique'            => 'O CNPJ informado já está cadastrado.',

            'status.required'        => 'O campo status é obrigatório.',
            'status.integer'         => 'O campo status deve ser um número inteiro.',
            'status.in'              => 'O campo status deve ser 1 (ativo) ou 2 (inativo).',
        ];
    }
}
