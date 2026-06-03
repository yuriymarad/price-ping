<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracked_tickers', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('long_name')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('exchange_name', 50)->nullable();
            $table->decimal('last_price', 12, 4)->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracked_tickers');
    }
};
