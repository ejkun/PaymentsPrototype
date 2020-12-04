<?php

namespace App\Http\Requests;

use App\Rules\Cnpj;
use App\Rules\Cpf;
use App\Rules\OneOf;
use App\Rules\PasswordConfirmation;
use Illuminate\Foundation\Http\FormRequest;

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
            'document' => [
                'required',
                new OneOf("The data must be a valid CPF or CNPJ", new Cpf(), new Cnpj())
            ],
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
