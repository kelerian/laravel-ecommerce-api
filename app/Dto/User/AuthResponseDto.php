<?php

namespace App\Dto\User;

use App\Models\Users\User;

class AuthResponseDto
{
    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly string $expiresAt,
    )
    {}

}
