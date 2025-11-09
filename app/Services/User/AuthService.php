<?php

namespace App\Services\User;

use App\Dto\User\RegisterDto;
use App\Dto\User\AuthResponseDto;
use App\Exceptions\BusinessException;
use App\Http\Resources\Users\UserResource;
use App\Models\Users\PlusUser;
use App\Models\Users\Profile;
use App\Models\Users\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    public function createUser(RegisterDto $dto, string $userAgent, string $ip): AuthResponseDto
    {
        $user = DB::transaction(function () use ($dto){
            $userData = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->password
            ]);

            $plusUserData = PlusUser::create([
                'lastname' => $dto->lastname,
                'birthday' => $dto->birthday,
                'address' => $dto->address,
                'user_id' => $userData->id,
                'gender_slug' => $dto->gender
            ]);

            $profile = Profile::create([
                'user_id' => $userData->id,
                'phone' => $dto->phone,
                'address' => $dto->company_address,
                'inn' => $dto->inn,
                'title' => $dto->title,
            ]);
            $userData->addToGroupBySlug('default_users');

            $userData->cart()->create([
                'fuser_id' => $dto->fuser_id,
            ]);
            $userData->load(['plusUser', 'profiles', 'groups', 'cart']);
            event(new Registered($userData));

            return $userData;
        });

        auth()->login($user);

        $token = $this->createToken($userAgent, $ip);
        $expiresAt = $this->getTokenExpiry()->toISOString();

        return new AuthResponseDto(
            user: $user,
            token: $token,
            expiresAt: $expiresAt
        );

    }
    public function getTokenExpiry()
    {
        return now()->addDays(30);
    }

    public function createToken(string $userAgent, string $ip): string
    {
        $deviceName = $userAgent . '|' . $ip;

        $token = auth()->user()->createToken($deviceName,['*'],$this->getTokenExpiry())->plainTextToken;
        return $token;
    }

    public function login(string $email, string $password, string $userAgent, string $ip): AuthResponseDto
    {
        $user = User::where('email', $email)->firstOrFail();

        if (!$user || !Hash::check($password, $user->password) ){
            throw new BusinessException('The provided credentials are incorrect',[],400);
        }
        $user->load(['plusUser', 'profiles','groups', 'cart']);

        auth()->login($user);

        $token = $this->createToken($userAgent, $ip);

        event(new Login('sanctum',$user,false));

        $expiresAt = $this->getTokenExpiry()->toISOString();

        return new AuthResponseDto(
            user: $user,
            token: $token,
            expiresAt: $expiresAt
        );
    }
}
