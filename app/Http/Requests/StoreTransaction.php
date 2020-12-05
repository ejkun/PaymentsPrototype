<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransaction extends FormRequest
{
    public User $payer;
    public User $payee;

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
            'payer_id' => 'required|exists:users,id',
            'payee_id' => 'required|exists:users,id',
            'value' => 'numeric|min:0|not_in:0'
        ];
    }

    protected function passedValidation()
    {
        $this->payer = User::findOrFail($this['payer_id']);
        $this->payee = User::findOrFail($this['payee_id']);
    }
}
