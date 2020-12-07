<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'payer_id' => [
                'required',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query->where('type', User::REGULAR);
                }),
            ],
            'payee_id' => 'required|exists:users,id',
            'value' => 'numeric|min:0|not_in:0'
        ];
    }

    protected function passedValidation()
    {
        $this->payer = User::where([
            'id' => $this['payer_id']
        ])->firstOrFail();

        $this->payee = User::where([
            'id' => $this['payee_id']
        ])->firstOrFail();
    }
}
