<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns|max:50',
            'phone' => 'required|string|regex:/^\+7\d{10}$/',
            'address' => 'required|string|max:255|min:1',
            'pay_type' => 'required|string|in:online,offline',
            'fuser_id' => 'required|string|exists:carts,fuser_id'
        ];
    }
}
