<?php

namespace App\Dto\User;

class RegisterDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $lastname,
        public readonly string $birthday,
        public readonly string $address,
        public readonly string $gender,
        public readonly string $phone,
        public readonly string $company_address,
        public readonly string $inn,
        public readonly string $title,
        public readonly string $fuser_id,
    )
    {}

    public static function fromArray($array)
    {
        return new self(
            name: $array['name'],
            email: $array['email'],
            password: $array['password'],
            lastname: $array['lastname'],
            birthday: $array['birthday'],
            address: $array['address'],
            gender: $array['gender'],
            phone: $array['phone'],
            company_address: $array['company_address'],
            inn: $array['inn'],
            title: $array['title'],
            fuser_id: $array['fuser_id'],
        );
    }
}
