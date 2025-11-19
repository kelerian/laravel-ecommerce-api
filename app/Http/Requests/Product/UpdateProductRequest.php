<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'title' => [
                'sometimes',
                'string',
                'min:2',
                'max:100',
                Rule::unique('products', 'title')->ignore($this->route('slug'), 'slug')
            ],
            'description' => 'sometimes|string|min:5',
            'preview_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:10240|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'price_opt' => 'sometimes|numeric|decimal:0,2',
            'price_special' => 'sometimes|numeric|decimal:0,2',
            'price_rozn' => 'sometimes|numeric|decimal:0,2',
            'stock_krasnodar' => 'sometimes|integer|min:0',
            'stock_moskov' => 'sometimes|integer|min:0',
            'stock_ivanovo' => 'sometimes|integer|min:0',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['title'] = isset($data['title']) ? $data['title'] : null;
        $data['description'] = isset($data['description']) ? $data['description'] : null;

        return $data;
    }
}
