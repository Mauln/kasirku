<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['plan_id', 'feature_id'], 'uk_plan_feature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
