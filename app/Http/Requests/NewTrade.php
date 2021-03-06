<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewTrade extends FormRequest
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
            'amount'            => 'required|numeric|min:0.1',
            'from_currency'     => ['required', 'string', Rule::in(Currency::AVAILABLE_CURRENCIES), 'different:to_currency'],
            'rate'              => 'required|numeric|min:0.1',
            'to_currency'       => ['required', 'string', Rule::in(Currency::AVAILABLE_CURRENCIES), 'different:from_currency'],
            'rate_base_currency'    => [
                'string', 
                function ($attribute, $value, $fail) {
                    if ($value && $value != request('from_currency') && $value != request('to_currency')) {
                        $fail("$attribute is invalid, it must be same with from_currency or to_currency.");
                    }
                },
            ],
        ];
    }
}
