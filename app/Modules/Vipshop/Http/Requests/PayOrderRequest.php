<?php

namespace App\Modules\Vipshop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    
    public function authorize()
    {
        return true;
    }

    
    public function rules()
    {
        return [
            'password' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'password.required' => '请输入支付密码'
        ];
    }
}
