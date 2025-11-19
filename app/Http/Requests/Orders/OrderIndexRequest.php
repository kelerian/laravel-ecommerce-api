<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class OrderIndexRequest extends FormRequest
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
            'limit' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1',
            'sort' => 'sometimes|string|in:created_at,final_price',
            'direction' => 'sometimes|string|in:asc,desc',
            'email' => 'sometimes|email',
            'user_id' => 'sometimes|exists:users,id',
            'all_orders' => 'sometimes|in:true,false',
            'pay_type' => 'sometimes|string|exists:pay_types,slug',
            'order_status' => 'sometimes|string|exists:order_statuses,slug',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date'
        ];
    }



    public function withValidator($validator)
    {
        $validator->after(function ($validator){

            $user = $this->user();

            if ($this->filled('user_id') && !$user->isAdmin()) {
                $validator->errors()->add(
                    'user_id',
                    'Filtering by user ID is available only for administrators'
                );
            }

            $allOrdersFlag = filter_var($this->boolean('all_orders') ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($allOrdersFlag && !$user->isAdmin()) {
                $validator->errors()->add(
                    'all_orders',
                    'Viewing all orders is available only for administrators'
                );
            }
        });
    }

}
