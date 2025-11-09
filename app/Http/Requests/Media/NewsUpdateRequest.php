<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class NewsUpdateRequest extends FormRequest
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
            'title' => 'sometimes|string|min:5|max:100|unique:news,title',
            'content' => 'sometimes|string|min:5',
            'detail_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:10240|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'preview_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:10240|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240|dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000',
            'tags' => 'string',
        ];
    }

    public function messages()
    {
        return [
            '*.image' => 'Файл должен быть изображением',
            '*.mimes' => 'Допустимые форматы: JPEG, PNG, JPG, GIF, WebP',
            '*.max' => 'Максимальный размер файла: 10MB',
            '*.dimensions' => 'Изображение должно быть от 100x100 до 5000x5000 пикселей',
            'tags.array' => 'Теги должны быть переданы в виде массива',
            'tags.*.string' => 'Указанный тег не существует',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['tags'] = $data['tags'] ?? false;

        return $data;
    }
}
