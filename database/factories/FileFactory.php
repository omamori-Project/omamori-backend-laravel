<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Omamori;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $key = 'exports/' . Str::uuid()->toString() . '.png';

        return [
            'user_id' => User::factory(),
            'omamori_id' => Omamori::factory(),
            'purpose' => 'render_output',
            'visibility' => 'private',
            'file_key' => $key,
            'url' => 'http://localhost/storage/' . $key,
            'content_type' => 'image/png',
            'size_bytes' => null,
            'width' => null,
            'height' => null,
            'created_at' => now(),
            'deleted_at' => null,
        ];
    }
}