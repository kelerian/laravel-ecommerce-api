<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class IndexNewsRequest extends FormRequest
{
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
            'limit' => 'integer|min:1',
            'page' => 'integer|min:1',
            'sort' => 'string|in:created_at',
            'direction' => 'string|in:asc,desc',
            'tags' => 'array',
            'tags.*' => 'string|exists:tags,slug',
            'tags_flag' => 'string|in:true,false',
            'user_email' => 'email',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date'
        ];
    }

}
