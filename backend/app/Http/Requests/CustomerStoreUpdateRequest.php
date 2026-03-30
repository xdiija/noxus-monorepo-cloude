<?php

namespace App\Http\Requests;

use App\Helpers\CpfHelper;
use App\Helpers\PhoneHelper;
use App\Rules\CpfRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerStoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf'     => CpfHelper::sanitize($this->cpf),
            'phone_1' => PhoneHelper::sanitize($this->phone_1),
            'phone_2' => PhoneHelper::sanitize($this->phone_2),
        ]);
    }

    public function rules(): array
    {
        $customerId = $this->route('id');

        return [
            'name'    => ['required', 'string', 'min:3', 'max:255'],
            'email'   => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'cpf'     => [
                'required',
                'max:50',
                Rule::unique('customers', 'cpf')->ignore($customerId),
                new CpfRule(),
            ],
            'phone_1' => ['nullable', new PhoneRule()],
            'phone_2' => ['nullable', new PhoneRule()],
            'status'  => ['required', 'integer', Rule::in([1, 2])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'O campo nome é obrigatório.',
            'name.min'         => 'O campo nome deve ter no mínimo 3 caracteres.',
            'name.max'         => 'O campo nome deve ter no máximo 255 caracteres.',

            'email.required'   => 'O campo e-mail é obrigatório.',
            'email.email'      => 'O campo e-mail deve conter um endereço válido.',
            'email.max'        => 'O campo e-mail deve ter no máximo 255 caracteres.',
            'email.unique'     => 'O e-mail informado já está em uso.',

            'cpf.required'     => 'O campo CPF é obrigatório.',
            'cpf.max'          => 'O campo CPF deve ter no máximo 50 caracteres.',
            'cpf.unique'       => 'O CPF informado já está cadastrado.',

            'phone_1.nullable' => 'O campo telefone 1 é inválido.',
            'phone_2.nullable' => 'O campo telefone 2 é inválido.',

            'status.required'  => 'O campo status é obrigatório.',
            'status.integer'   => 'O campo status deve ser um número inteiro.',
            'status.in'        => 'O campo status deve ser 1 (ativo) ou 2 (inativo).',
        ];
    }
}
