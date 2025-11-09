<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $userId = $this->user()->id;
        return [
            'name' => 'sometimes|string|max:30|min:1',
            'lastname' => 'sometimes|string|max:30|min:1',
            'email' => [
                'sometimes',
                'email;rfc,dns',
                Rule::unique('users', 'email')->ignore($userId),
                'max:50'
            ],
            'birthday' => 'sometimes|date|before:-18 years|after:-100 years',
            'address' => 'sometimes|string|max:255|min:1',
            'gender_slug' => 'sometimes|exists:genders,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'birthday.before' => 'Некорректная дата дня рождения! Вам должно быть не менее 1го дня :)',
            'birthday.after' => 'Некорректная дата дня рождения! Вы не можете быть из будущего...',
            'email.email' => 'Некорректная доменная запись для электронного адреса',
            'email.unique' => 'Такой email уже используется',
            'gender.exists' => 'Некорректное значение поля Пол',
            '*.max' => 'Поле :attribute не должно превышать :max символов',
            '*.min' => 'Поле :attribute должно содержать минимум :min символов',
            '*.string' => 'Поле :attribute должно быть строкой',
            '*.date' => 'Поле :attribute должно быть корректной датой',
        ];
    }
}
