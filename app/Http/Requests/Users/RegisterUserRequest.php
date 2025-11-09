<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|max:30|min:1',
            'lastname' => 'required|string|max:30|min:1',
            'email' => 'required|email:rfc,dns|unique:users,email|max:50',
            'birthday' => 'required|date|before:-18 years|after:-100 years',
            'address' => 'required|string|max:255|min:1',
            'gender' => 'required|exists:genders,slug',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ],
            'phone' => 'required|string|regex:/^\+7\d{10}$/|unique:profiles,phone',
            'company_address' => 'required|string|max:255|min:1',
            'inn' => 'required|string|size:12|unique:profiles,inn',
            'title' => 'required|string|unique:profiles,title|max:150',
            'fuser_id' => 'required|string|unique:carts,fuser_id'
        ];
    }

    public function messages(): array
    {
        return [
            'birthday.before' => 'Некорректная дата дня рождения! Вам должно быть не менее 1го дня :)',
            'birthday.after' => 'Некорректная дата дня рождения! Вы не можете быть из будущего...',
            'phone.unique' => 'Пользователь с таким телефоном уже существует',
            'phone.regex' => 'Телефон должен быть в формате +7XXXXXXXXXX',
            'email.email' => 'Некорректная доменная запись для электронного адреса',
            'email.unique' => 'Такой email уже используется',
            'gender.exists' => 'Некорректное значение поля Пол',
            'inn.unique' => 'Такой ИНН уже зарегистрирован',
            'inn.size' => 'ИНН должен содержать 12 цифр',
            'title.unique' => 'Такое название компании уже существует',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            '*.required' => 'Поле :attribute обязательно для заполнения',
            '*.max' => 'Поле :attribute не должно превышать :max символов',
            '*.min' => 'Поле :attribute должно содержать минимум :min символов',
            '*.string' => 'Поле :attribute должно быть строкой',
            '*.date' => 'Поле :attribute должно быть корректной датой',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Имя',
            'lastname' => 'Фамилия',
            'email' => 'Электронная почта',
            'birthday' => 'Дата рождения',
            'address' => 'Адрес',
            'gender' => 'Пол',
            'password' => 'Пароль',
            'password_confirmation' => 'Подтверждение пароля',
            'phone' => 'Номер телефона',
            'company_address' => 'Адрес компании',
            'inn' => 'ИНН',
            'title' => 'Название компании'
        ];
    }
}
