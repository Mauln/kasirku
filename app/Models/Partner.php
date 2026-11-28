<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'commission_rate',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
