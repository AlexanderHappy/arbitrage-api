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
        Schema::create('arbitrage_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('pair', 20)->index(); // BTC/USDT
            $table->foreignId('buy_exchange_id')->index()->constrained('exchanges');
            $table->foreignId('sell_exchange_id')->index()->constrained('exchanges');
            $table->decimal('buy_price', 20, 8);
            $table->decimal('sell_price', 20, 8);
            $table->decimal('spread_amount', 20, 8)->index();
            $table->decimal('spread_percentage', 8, 4)->index();
            $table->decimal('profit_after_fees', 20, 8);
            $table->decimal('volume', 20, 8);
            $table->decimal('total_fees', 20, 8);
            $table->enum('status', ['active', 'expired', 'executed'])->default('active')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitrage_opportunities');
    }
};
