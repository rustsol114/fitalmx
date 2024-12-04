<?php


namespace Modules\BlockIo\Http\Requests;

use App\Http\Requests\CustomFormRequest;

class CryptoSendRequest extends CustomFormRequest
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
            'receiverAddress' => 'required',
            'amount' => 'required',
            'senderAddress' => 'required',
            'walletCurrencyCode' => 'required',
            'priority' => 'required'
        ];
    }
}
