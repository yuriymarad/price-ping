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
        Schema::table('tracked_tickers', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->nullable()->after('is_in_portfolio');
            $table->decimal('average_price', 12, 4)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracked_tickers', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'average_price']);
        });
    }
};
