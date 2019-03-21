<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

class TwoFactorRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required|numeric'
        ];
    }
}
