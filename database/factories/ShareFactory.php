<?php

namespace Database\Factories;

use App\Models\Omamori;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Share>
 */
class ShareFactory extends Factory
{
    protected $model = Share::class;

    public function definition(): array
    {
        return [
            'omamori_id' => Omamori::factory(),
            'user_id'    => User::factory(),
            'token'      => (string) Str::uuid(),
            'is_active'  => true,
            'expires_at' => null,
            'view_count' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subMinute()]);
    }

    public function withOmamori(Omamori $omamori): static
    {
        return $this->state(fn () => ['omamori_id' => $omamori->id]);
    }

    public function withUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}