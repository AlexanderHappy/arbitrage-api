<?php

namespace App\Services\Exchange;

use App\Interfaces\InterfaceExchangeStrategy;
use App\Dto\DtoPriceData;
use App\Exceptions\ExchangeApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KucoinStrategy implements InterfaceExchangeStrategy
{
    private const API_URL = 'https://api.kucoin.com';
    private const TIMEOUT = 10;

    public function __construct(
        private ?string $apiKey = null,
        private ?string $apiSecret = null,
        private ?string $passphrase = null
    )
    {
    }

    public function getPrice(string $pair): DtoPriceData
    {
        try {
            $symbol = $this->formatPair($pair);

            $response = Http::timeout(self::TIMEOUT)
                ->get(
                    self::API_URL . '/api/v1/market/orderbook/level1',
                    [
                        'symbol' => $symbol
                    ]
                );

            if (!$response->successful()) {
                throw new ExchangeApiException("KuCoin API error: " . $response->body());
            }

            $data = $response->json('data');

            if (!$data) {
                throw new ExchangeApiException("Invalid response from KuCoin");
            }

            // Get 24h stats for volume
            $statsResponse = Http::timeout(self::TIMEOUT)
                ->get(
                    self::API_URL . '/api/v1/market/stats',
                    [
                        'symbol' => $symbol
                    ]
                );

            $stats = $statsResponse->json(
                'data',
                []
            );

            return new DtoPriceData(
                pair:      $pair,
                bid:       (float)$data['bestBid'],
                ask:       (float)$data['bestAsk'],
                last:      (float)$data['price'],
                volume24h: (float)($stats['vol'] ?? 0),
                exchange:  $this->getName(),
                quotedAt:  new \DateTime()
            );

        } catch (\Exception $e) {
            Log::error(
                'KuCoin API error',
                [
                    'pair' => $pair,
                    'error' => $e->getMessage()
                ]
            );
            throw new ExchangeApiException("Failed to get price from KuCoin: " . $e->getMessage());
        }
    }

    public function getOrderBook(
        string $pair,
        int    $limit = 10
    ): array
    {
        try {
            $symbol = $this->formatPair($pair);

            $response = Http::timeout(self::TIMEOUT)
                ->get(
                    self::API_URL . '/api/v1/market/orderbook/level2_20',
                    [
                        'symbol' => $symbol
                    ]
                );

            if (!$response->successful()) {
                throw new ExchangeApiException("KuCoin API error: " . $response->body());
            }

            $data = $response->json('data');

            return [
                'bids' => array_slice(
                    $data['bids'] ?? [],
                    0,
                    $limit
                ),
                'asks' => array_slice(
                    $data['asks'] ?? [],
                    0,
                    $limit
                ),
                'timestamp' => $data['time'] ?? time() * 1000
            ];

        } catch (\Exception $e) {
            Log::error(
                'KuCoin order book error',
                [
                    'pair' => $pair,
                    'error' => $e->getMessage()
                ]
            );
            throw new ExchangeApiException("Failed to get order book from KuCoin: " . $e->getMessage());
        }
    }

    public function get24hVolume(string $pair): float
    {
        try {
            $symbol = $this->formatPair($pair);

            $response = Http::timeout(self::TIMEOUT)
                ->get(
                    self::API_URL . '/api/v1/market/stats',
                    [
                        'symbol' => $symbol
                    ]
                );

            if (!$response->successful()) {
                throw new ExchangeApiException("KuCoin API error: " . $response->body());
            }

            return (float)$response->json(
                'data.vol',
                0
            );

        } catch (\Exception $e) {
            Log::error(
                'KuCoin volume error',
                [
                    'pair' => $pair,
                    'error' => $e->getMessage()
                ]
            );
            return 0;
        }
    }

    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get(self::API_URL . '/api/v1/status');

            return $response->successful() &&
                $response->json('data.status') === 'open';

        } catch (\Exception $e) {
            Log::error(
                'KuCoin health check failed',
                [
                    'error' => $e->getMessage()
                ]
            );
            return false;
        }
    }

    public function getName(): string
    {
        return 'KuCoin';
    }

    public function formatPair(string $pair): string
    {
        // KuCoin uses dash instead of slash: BTC-USDT
        return str_replace(
            '/',
            '-',
            $pair
        );
    }
}
