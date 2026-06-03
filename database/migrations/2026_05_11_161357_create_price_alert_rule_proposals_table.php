<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('price_alert_rule_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracked_ticker_id')->constrained()->cascadeOnDelete();
            $table->string('rule_type', 20);
            $table->decimal('threshold_price', 12, 4)->nullable();
            $table->string('threshold_direction', 10)->nullable();
            $table->decimal('percent_value', 8, 4)->nullable();
            $table->unsignedSmallInteger('period_hours')->nullable();
            $table->string('percent_direction', 10)->nullable();
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->boolean('is_one_shot')->default(false);
            $table->timestamps();

            $table->index('tracked_ticker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_alert_rule_proposals');
    }
};
