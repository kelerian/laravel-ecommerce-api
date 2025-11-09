<?php

namespace Tests\Feature;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_register_user(): void
    {

        $userData = [
            'email' => 'test_' . time() . '@mail.ru',
            'name' => 'Username',
            'lastname' => 'Secondname',
            'birthday' => '1990-01-01',
            'phone' => '+7999' . rand(1000000, 9999999),
            'address' => 'Transaction Address',
            'gender' => 'male',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'title' => 'OOO pd',
            'company_address' => 'Moskva, Lenina, 1',
            'inn' => '1234567890' . rand(10, 99),
            'fuser_id' => strval(time())
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201);
    }

    public function test_user_can_login_with_correct_credentials()
    {
        User::factory()
            ->withPlusUser()
            ->withTwoProfiles()
            ->create([
                'email' => 'testlogin@mail.ru',
                'password' => 'Password123!'
            ]);

        $loginData = [
            'email' => 'testlogin@mail.ru',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
                'expires_at'
            ]
        ]);

        $this->assertNotEmpty($response->json('data.token'));
    }


    public function test_login_fails_with_wrong_password(): void
    {
        $email = 'test'.time().'@mail.ru';
        User::factory()->create([
            'email' => $email,
            'password' => 'Password123!'
        ]);

        $loginData = [
            'email' => $email,
            'password' => 'WrongPassword!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()
            ->withPlusUser()
            ->withTwoProfiles()
            ->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        $invalidData = [
            'email' => 'testes',
            'password' => 'tetfh',
            'password_confirmation' => 'fdghfhgdfhg'
        ];

        $response = $this->postJson('/api/v1/auth/register', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $email = 'test2'.time().'@mail.ru';
        User::factory()
            ->withPlusUser()
            ->withTwoProfiles()
            ->create([
            'email' => $email
        ]);

        $userData = [
            'email' => $email,
            'name' => 'Test User',
            'lastname' => 'Testov',
            'birthday' => '1990-01-01',
            'phone' => '+71991234567',
            'address' => 'Test Address',
            'gender' => 'female',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'title' => 'OOO Google',
            'company_address' => 'California, Georga, 3',
            'inn' => '312256789012',
            'fuser_id' => strval(time())
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_refresh_token(): void
    {
        $email = 'test2'.time().'@mail.ru';
        $user = User::factory()
            ->withPlusUser()
            ->withTwoProfiles()
            ->create([
                'email' => $email,
                'password' => 'Password123!'
            ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'accept' => 'application/json'
            ])->postJson('/api/v1/auth/refreshToken');

        $response->assertStatus(201);
    }

}
