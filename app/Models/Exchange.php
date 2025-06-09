<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exchange extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'api_key',
        'api_secret',
        'passphrase',
        'is_active',
        'rate_limit',
        'trading_fee',
        'withdrawal_fee',
        'min_trade_amount',
        'supported_pairs',
        'last_health_check',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit' => 'integer',
        'trading_fee' => 'decimal:4',
        'withdrawal_fee' => 'decimal:8',
        'min_trade_amount' => 'decimal:8',
        'supported_pairs' => 'array',
        'last_health_check' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'passphrase',
    ];

    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(PriceSnapshot::class);
    }

//    public function buyArbitrageOpportunities(): HasMany
//    {
//        return $this->hasMany(ArbitrageOpportunity::class, 'buy_exchange_id');
//    }

//    public function sellArbitrageOpportunities(): HasMany
//    {
//        return $this->hasMany(ArbitrageOpportunity::class, 'sell_exchange_id');
//    }

//    public function healthChecks(): HasMany
//    {
//        return $this->hasMany(ExchangeHealthCheck::class);
//    }
}
