<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracked_ticker_id')->constrained()->cascadeOnDelete();

            $table->string('rule_type', 20);
            $table->boolean('is_active')->default(true);

            // Threshold rule fields
            $table->decimal('threshold_price', 12, 4)->nullable();
            $table->string('threshold_direction', 10)->nullable(); // 'above' | 'below'

            // Percent change rule fields
            $table->decimal('percent_value', 8, 4)->nullable();
            $table->unsignedSmallInteger('period_hours')->nullable();
            $table->string('percent_direction', 10)->nullable(); // 'up' | 'down' | 'either'

            // Baseline tracking for percent_change (no separate history table)
            $table->decimal('baseline_price', 12, 4)->nullable();
            $table->timestamp('baseline_recorded_at')->nullable();

            // Cooldown and alerting state
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();

            $table->index(['tracked_ticker_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alert_rules');
    }
};
