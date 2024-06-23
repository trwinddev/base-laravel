<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use App\Http\Requests\CommonRequest;
use Illuminate\Http\Request;

class LoginUserRequest extends CommonRequest
{
    use ApiResponseTrait;
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
    public function rules(Request $request)
    {
        $ruleArr = [
            'password' => 'required',
            'email' => 'required',
        ];

        return $ruleArr;
    }

    /**
     * get the validation messages that apply to the request attributes
     * @return array
     */
    public function messages()
    {
        return [];
    }
}
