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
        Schema::create('membership_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            
            $table->string('payment_id')->nullable();
            $table->string('payment_provider')->nullable();
            
            $table->string('billing_cycle');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            $table->string('status')->default('pending'); // pending, processing, paid, failed, cancelled, refunded
            $table->json('metadata')->nullable();
            
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_purchases');
    }
};
