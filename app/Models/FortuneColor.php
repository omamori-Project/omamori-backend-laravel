<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $hex
 * @property string|null $category
 * @property string|null $short_meaning
 * @property string|null $meaning
 * @property array<int, mixed> $tips
 * @property bool $is_active
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 * @property \Carbon\CarbonInterface|null $deleted_at
 */
class FortuneColor extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'hex',
        'category',
        'short_meaning',
        'meaning',
        'tips',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tips' => 'array',
        'is_active' => 'boolean',
    ];
}