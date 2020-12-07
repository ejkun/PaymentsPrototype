<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use App\Rules\OneOf;
use App\Rules\PasswordConfirmation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'type' => [
                'required',
                Rule::in([
                    User::REGULAR,
                    User::MERCHANT
                ])
            ],
            'document' => [
                'required',
                'unique:users',
                new OneOf("The field :attribute must be a valid CPF or CNPJ", new Cpf(), new Cnpj()),
            ],
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
