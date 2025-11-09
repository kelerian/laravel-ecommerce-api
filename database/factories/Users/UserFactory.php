<?php

namespace Database\Factories\Users;

use App\Models\Users\Gender;
use App\Models\Users\PlusUser;
use App\Models\Users\Profile;
use App\Models\Users\User;
use App\Models\Users\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withPlusUser(array $plusUserData = []): static
    {
        return $this->afterCreating(function  ($user) use ($plusUserData){
            PlusUser::factory()->create(array_merge(
                ['user_id' => $user->id],
                    $plusUserData)
            );
        });

    }
    public function withTwoProfiles(array $profileData = []): static
    {
        return $this->afterCreating(function  ($user) use ($profileData){
            Profile::factory(2)->create(array_merge(
                    ['user_id' => $user->id],
                    $profileData)
            );
        });

    }
    public function withGenders(): static
    {
        return $this->afterCreating(function ($user) {
            $genders = Gender::get();

            if ($genders->isEmpty()) {
                Gender::create(['gender_type' => 'Мужской', 'slug' => 'male']);
                Gender::create(['gender_type' => 'Женский', 'slug' => 'female']);
            } elseif ($genders->count() < 2) {
                $existingNames = $genders->pluck('gender_type')->toArray();
                if (!in_array('Мужской', $existingNames)) {
                    Gender::create(['gender_type' => 'Мужской', 'slug' => 'male']);
                }
                if (!in_array('Женский', $existingNames)) {
                    Gender::create(['gender_type' => 'Женский', 'slug' => 'female']);
                }
            }
        });
    }

    public function withUserGroups(): static
    {
        return $this->afterCreating(function ($user) {
            $userGroups = UserGroup::get();

            if ($userGroups->isEmpty()) {
                UserGroup::create(['title' => 'Администраторы', 'slug' => 'admin']);
                UserGroup::create(['title' => 'Пользователи', 'slug' => 'default_users']);
                UserGroup::create(['title' => 'Менеджеры', 'slug' => 'managers']);
            } elseif ($userGroups->count() < 3) {
                $existingNames = $userGroups->pluck('slug')->toArray();
                if (!in_array('admin', $existingNames)) {
                    UserGroup::create(['title' => 'Администраторы', 'slug' => 'admin']);
                }
                if (!in_array('default_users', $existingNames)) {
                    UserGroup::create(['title' => 'Пользователи', 'slug' => 'default_users']);
                }
                if (!in_array('managers', $existingNames)) {
                    UserGroup::create(['title' => 'Менеджеры', 'slug' => 'managers']);
                }
            }

            $user->addToGroupBySlug('default_users');
        });
    }
    public function withFuser(): static
    {
        return $this->afterCreating(function ($user) {

            $user->cart()->create([
                'fuser_id' => strval(time()),
            ]);
        });
    }

}
