<?php

namespace App\Services\Exchange;

use App\Interfaces\InterfaceExchangeStrategy;
use App\Dto\DtoPriceData;
use App\Models\Exchange;
use App\Models\PriceSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeService
{
    private InterfaceExchangeStrategy $strategy;
    private Exchange                  $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
        $this->strategy = $this->createStrategy($exchange);
    }

    /**
     * Get current price with caching
     */
    public function getPrice(string $pair): DtoPriceData
    {
        $cacheKey = "price:{$this->exchange->name}:{$pair}";

        return Cache::remember(
            $cacheKey,
            10,
            function () use
            (
                $pair
            ) {
                $price = $this->strategy->getPrice($pair);

                // Store snapshot
                $this->storePriceSnapshot($price);

                return $price;
            }
        );
    }

    /**
     * Get order book
     */
    public function getOrderBook(
        string $pair,
        int    $limit = 10
    ): array
    {
        return $this->strategy->getOrderBook(
            $pair,
            $limit
        );
    }

    /**
     * Check exchange health
     */
    public function healthCheck(): bool
    {
        $isHealthy = $this->strategy->healthCheck();

        $this->exchange->update(
            [
                'last_health_check' => now(),
                'is_active' => $isHealthy
            ]
        );

        return $isHealthy;
    }

    /**
     * Store price snapshot in database
     */
    private function storePriceSnapshot(DtoPriceData $priceData): void
    {
        try {
            PriceSnapshot::create(
                [
                    'exchange_id' => $this->exchange->id,
                    'pair' => $priceData->pair,
                    'price' => $priceData->last,
                    'volume_24h' => $priceData->volume24h,
                    'quoted_at' => $priceData->quotedAt
                ]
            );
        } catch (\Exception $e) {
            Log::error(
                'Failed to store price snapshot',
                [
                    'exchange' => $this->exchange->name,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Create strategy based on exchange
     */
    private function createStrategy(Exchange $exchange): InterfaceExchangeStrategy
    {
        return match ($exchange->name) {
            'KuCoin' => new KucoinStrategy(
                $exchange->api_key,
                $exchange->api_secret,
                $exchange->passphrase
            ),
//            'Binance' => new BinanceStrategy(
//                $exchange->api_key,
//                $exchange->api_secret
//            ),
//            'Kraken' => new KrakenStrategy(
//                $exchange->api_key,
//                $exchange->api_secret
//            ),
            default => throw new \InvalidArgumentException("Unknown exchange: {$exchange->name}")
        };
    }

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }
}
