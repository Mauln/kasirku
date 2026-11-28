<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'monthly_price',
        'yearly_price',
        'max_pos',
        'max_employees',
        'max_admins',
        'trial_days',
        'is_trial_plan',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'max_pos' => 'integer',
            'max_employees' => 'integer',
            'max_admins' => 'integer',
            'trial_days' => 'integer',
            'is_trial_plan' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('is_enabled');
    }
}
