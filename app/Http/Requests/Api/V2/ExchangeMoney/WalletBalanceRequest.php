<?php



namespace App\Http\Requests\Api\V2\ExchangeMoney;

use App\Http\Requests\CustomFormRequest;

class WalletBalanceRequest extends CustomFormRequest
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
            'from_currency' => 'required|numeric|min:0|not_in:0',
            'to_currency' => 'required|numeric|min:0|not_in:0',
        ];
    }
}
