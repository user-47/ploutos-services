<?php

namespace App\Http\Requests;

use App\Managers\CurrencyManager;
use Illuminate\Foundation\Http\FormRequest;

class AcceptTrade extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorize if user did not create the trade and the trade is open or partially filled
        return request()->trade->user_id != request()->user()->id && request()->trade->isAcceptable;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => [
                'required', 
                'numeric', 
                'min:0.1',
                function($attribute, $value, $fail) {
                    $minorValue = CurrencyManager::toMinor($value, request()->trade->from_currency);
                    if ($minorValue > request()->trade->availableAmount) {
                        $fail($attribute . ' is greater than available trade amount.');
                    }
                }
            ],
        ];
    }
}
