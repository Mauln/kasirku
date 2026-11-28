<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_sessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('session_id', 40)->unique();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('mfa_verified_at')->nullable();
            $table->timestamp('reauth_at')->nullable()->comment('untuk aksi kritikal PROD');
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('admin_user_id', 'idx_admin');
            $table->index('revoked_at', 'idx_revoked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_sessions');
    }
};
