<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'mfa_secret',
        'mfa_recovery_codes',
        'mfa_enabled_at',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'mfa_secret',
        'mfa_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mfa_enabled_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AdminSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AdminSession::class);
    }
}
