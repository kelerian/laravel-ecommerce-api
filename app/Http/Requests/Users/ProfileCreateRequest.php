<?php

namespace App\Http\Requests\Users;

use App\Models\Users\Profile;
use Illuminate\Foundation\Http\FormRequest;

class ProfileCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create',Profile::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|regex:/^\+7\d{10}$/|unique:profiles,phone',
            'address' => 'required|string|max:255|min:1',
            'inn' => 'required|string|size:12|unique:profiles,inn',
            'title' => 'required|string|unique:profiles,title|max:150'
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Пользователь с таким телефоном уже существует',
            'phone.regex' => 'Телефон должен быть в формате +7XXXXXXXXXX',
            'inn.unique' => 'Такой ИНН уже зарегистрирован',
            'inn.size' => 'ИНН должен содержать 12 цифр',
            'title.unique' => 'Такое название компании уже существует',
            '*.required' => 'Поле :attribute обязательно для заполнения',
            '*.max' => 'Поле :attribute не должно превышать :max символов',
            '*.min' => 'Поле :attribute должно содержать минимум :min символов',
            '*.string' => 'Поле :attribute должно быть строкой',
        ];
    }

    public function attributes()
    {
        return [
            'address' => 'Адрес',
            'phone' => 'Номер телефона',
            'inn' => 'ИНН',
            'title' => 'Название компании'
        ];
    }
}
