<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'q' => 'required|string|max:255',
            'perPage' => 'sometimes|integer|min:1|max:1000',
            'page' => 'sometimes|integer|min:1|max:1000',
            'search_type' => 'required|string|in:fulltext,autocomplete',
            'models_type' => 'required|string|in:product,news'
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['page'] = isset($data['page']) ? $data['page'] : 1;
        $data['perPage'] = isset($data['perPage']) ? $data['perPage'] : 15;
        return $data;
    }
}
