<?php

namespace App\Http\Requests\Product;

use App\Models\Products\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
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
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('products', 'title')->ignore($this->route('slug'), 'slug')
            ],
            'description' => 'required|string|min:5',
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

        $data['price_opt'] = isset($data['price_opt']) ? (float)$data['price_opt'] : null;
        $data['price_special'] = isset($data['price_special']) ? (float)$data['price_special'] : null;
        $data['price_rozn'] = isset($data['price_rozn']) ? (float)$data['price_rozn'] : null;

        $data['stock_krasnodar'] = isset($data['stock_krasnodar']) ? (int)$data['stock_krasnodar'] : null;
        $data['stock_moskov'] = isset($data['stock_moskov']) ? (int)$data['stock_moskov'] : null;
        $data['stock_ivanovo'] = isset($data['stock_ivanovo']) ? (int)$data['stock_ivanovo'] : null;
        return $data;
    }
}
