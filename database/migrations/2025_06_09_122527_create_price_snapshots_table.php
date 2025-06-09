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
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->index()->constrained('exchanges')->onDelete('cascade');
            $table->string('pair', 20)->index(); // BTC/USDT
            $table->decimal('price', 20, 8);
            $table->decimal('volume_24h', 20, 8)->nullable(); // Объем торгов за последние 24 часа по этой валюте
            $table->timestamp('quoted_at')->index(); // Время котировки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
