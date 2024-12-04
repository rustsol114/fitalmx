<?php



namespace App\Http\Requests\Api\V2\DepositMoney;

use App\Http\Requests\CustomFormRequest;

class ValidateDepositRequest extends CustomFormRequest
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
            'currency_id' => 'required|numeric|min:0|not_in:0',
            'amount' => 'required|numeric',
            'payment_method' => 'required|numeric'
        ];
    }
}
