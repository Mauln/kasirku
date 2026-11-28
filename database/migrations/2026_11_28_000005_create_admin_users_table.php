<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('role', 30)->comment('super_admin/support/finance/ops/partnership');
            $table->string('mfa_secret', 255)->nullable();
            $table->text('mfa_recovery_codes')->nullable()->comment('encrypted JSON array 10 single-use codes');
            $table->timestamp('mfa_enabled_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'is_active'], 'idx_role_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
