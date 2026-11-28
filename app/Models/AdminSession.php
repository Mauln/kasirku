<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSession extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'admin_user_id',
        'ip_address',
        'user_agent',
        'mfa_verified_at',
        'reauth_at',
        'last_activity_at',
        'revoked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mfa_verified_at' => 'datetime',
            'reauth_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AdminUser, $this>
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }
}
