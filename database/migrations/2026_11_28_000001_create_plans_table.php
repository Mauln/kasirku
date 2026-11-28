<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->decimal('monthly_price', 15, 2)->default(0.00);
            $table->decimal('yearly_price', 15, 2)->nullable();
            $table->integer('max_pos')->default(1);
            $table->integer('max_employees')->default(1);
            $table->integer('max_admins')->default(1);
            $table->integer('trial_days')->default(14);
            $table->boolean('is_trial_plan')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'idx_active_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
