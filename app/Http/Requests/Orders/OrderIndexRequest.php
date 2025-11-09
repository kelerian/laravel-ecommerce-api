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

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['limit'] = $data['limit'] ?? 10;
        $data['direction'] = $data['direction'] ?? 'desc';
        $data['sort'] = $data['sort'] ?? 'created_at';
        $data['email'] = $data['email'] ?? false;
        $data['user_id'] = $data['user_id'] ?? false;
        $data['all_orders'] = filter_var($data['all_orders'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['pay_type'] = $data['pay_type'] ?? false;
        $data['order_status'] = $data['order_status'] ?? false;
        $data['date_to'] = $data['date_to'] ?? false;
        $data['date_from'] = $data['date_from'] ?? false;

        return $data;
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
