<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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

    public function member(?Member $member = null): static
    {
        return $this->afterMaking(function (User $user) use ($member): void {
            $user->forceFill([
                'role' => User::ROLE_MEMBER,
                'status' => User::STATUS_ACTIVE,
                'member_id' => $member?->id,
            ]);
        });
    }

    public function admin(): static
    {
        return $this->afterMaking(function (User $user): void {
            $user->forceFill([
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'member_id' => null,
            ]);
        });
    }

    public function pending(): static
    {
        return $this->afterMaking(function (User $user): void {
            $user->forceFill(['status' => User::STATUS_PENDING]);
        });
    }
}
