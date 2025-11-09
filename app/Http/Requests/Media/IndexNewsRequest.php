<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class IndexNewsRequest extends FormRequest
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

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['tags_flag'] = filter_var($data['tags_flag'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['page'] = $data['page'] ?? 1;
        $data['limit'] = $data['limit'] ?? 10;
        $data['direction'] = $data['direction'] ?? 'desc';
        $data['sort'] = $data['sort'] ?? 'created_at';
        $data['user_email'] = $data['user_email'] ?? false;
        $data['tags'] = $data['tags'] ?? false;
        $data['date_to'] = $data['date_to'] ?? false;
        $data['date_from'] = $data['date_from'] ?? false;

        return $data;
    }
}
