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
        // В данную таблицу будем добавлять все биржи с которыми система будет работать.
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->index(); // KuCoin, Gate
            $table->string('api_url');
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(1200);
            $table->decimal('trading_fee', 5, 4)->default(0.001);
            $table->decimal('withdrawal_fee', 15, 8)->default(0);
            $table->decimal('min_trade_amount', 15, 8)->default(0.001);
            $table->json('supported_pairs'); // ["BTC/USDT", "ETH/USDT"]
            $table->timestamp('last_health_check')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchanges');
    }
};
