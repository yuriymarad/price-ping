<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_alert_rules', function (Blueprint $table) {
            $table->dropForeign(['tracked_ticker_id']);
            $table->dropIndex(['tracked_ticker_id', 'is_active']);
            $table->renameColumn('tracked_ticker_id', 'ticker_id');
        });

        Schema::table('price_alert_rule_proposals', function (Blueprint $table) {
            $table->dropForeign(['tracked_ticker_id']);
            $table->dropIndex(['tracked_ticker_id']);
            $table->renameColumn('tracked_ticker_id', 'ticker_id');
        });

        Schema::rename('tracked_tickers', 'tickers');

        Schema::table('price_alert_rules', function (Blueprint $table) {
            $table->foreign('ticker_id')->references('id')->on('tickers')->cascadeOnDelete();
            $table->index(['ticker_id', 'is_active']);
        });

        Schema::table('price_alert_rule_proposals', function (Blueprint $table) {
            $table->foreign('ticker_id')->references('id')->on('tickers')->cascadeOnDelete();
            $table->index('ticker_id');
        });
    }

    public function down(): void
    {
        Schema::table('price_alert_rules', function (Blueprint $table) {
            $table->dropForeign(['ticker_id']);
            $table->dropIndex(['ticker_id', 'is_active']);
            $table->renameColumn('ticker_id', 'tracked_ticker_id');
        });

        Schema::table('price_alert_rule_proposals', function (Blueprint $table) {
            $table->dropForeign(['ticker_id']);
            $table->dropIndex(['ticker_id']);
            $table->renameColumn('ticker_id', 'tracked_ticker_id');
        });

        Schema::rename('tickers', 'tracked_tickers');

        Schema::table('price_alert_rules', function (Blueprint $table) {
            $table->foreign('tracked_ticker_id')->references('id')->on('tracked_tickers')->cascadeOnDelete();
            $table->index(['tracked_ticker_id', 'is_active']);
        });

        Schema::table('price_alert_rule_proposals', function (Blueprint $table) {
            $table->foreign('tracked_ticker_id')->references('id')->on('tracked_tickers')->cascadeOnDelete();
            $table->index('tracked_ticker_id');
        });
    }
};
