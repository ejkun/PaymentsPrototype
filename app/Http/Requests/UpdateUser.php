<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateUser extends FormRequest
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
            'name' => 'sometimes|string',
            'password' => 'sometimes|string|confirmed',
            'password_confirmation' => 'sometimes|required_with:password',
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $user = $this->route('user');
                    if (
                        $user instanceof User &&
                        !Hash::check($value, $user['password'])
                    ) {
                        $fail($attribute . " is invalid");
                    }
                }
            ]
        ];
    }
}
