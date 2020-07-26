<?php

namespace App\Http\Requests;

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
        return request()->trade->user_id != request()->user()->id;
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
                    if ($value > request()->trade->amount) {
                        $fail($attribute . ' is greater than trade amount.');
                    }
                }
            ],
        ];
    }
}
