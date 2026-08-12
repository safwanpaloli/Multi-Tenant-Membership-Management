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
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        
        if (Schema::hasColumn('tenants', 'payment_gateway_config')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('payment_gateway_config');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversing since we are replacing these with the new unified structure.
    }
};
