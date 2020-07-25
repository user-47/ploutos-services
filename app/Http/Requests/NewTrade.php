<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'from_currency'     => 'required|string',
            'rate'              => 'required|numeric|min:0.1',
            'to_currency'       => 'required|string',
        ];
    }
}
