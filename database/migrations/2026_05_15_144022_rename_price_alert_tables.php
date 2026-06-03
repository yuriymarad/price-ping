<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('price_alert_rules', 'alert_rules');
        Schema::rename('price_alert_rule_proposals', 'alert_rule_proposals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('alert_rules', 'price_alert_rules');
        Schema::rename('alert_rule_proposals', 'price_alert_rule_proposals');
    }
};
