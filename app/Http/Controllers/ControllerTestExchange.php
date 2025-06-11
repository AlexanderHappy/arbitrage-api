<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use App\Services\Exchange\ExchangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControllerTestExchange
{
    public function testKucoin(
        Request $request
    ): JsonResponse
    {
        $pair = $request->input(
            'pair',
            'BTC/USDT'
        );

        // Get or create KuCoin exchange
        $exchange = Exchange::first();

        $service = new ExchangeService($exchange);
        $results = [];

        // Test health check
        try {
            $health = $service->healthCheck();
            $results['health_check'] = [
                'status' => $health ? 'online' : 'offline',
                'checked_at' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            $results['health_check'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // Test price data
        try {
            $price = $service->getPrice($pair);
            $results['price_data'] = [
                'pair' => $price->pair,
                'bid' => $price->bid,
                'ask' => $price->ask,
                'last' => $price->last,
                'spread' => $price->getSpread(),
                'spread_percentage' => round(
                    $price->getSpreadPercentage(),
                    4
                ),
                'volume_24h' => $price->volume24h,
                'quoted_at' => $price->quotedAt->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $results['price_data'] = [
                'error' => $e->getMessage()
            ];
        }

        // Test order book
        try {
            $orderBook = $service->getOrderBook(
                $pair,
                5
            );
            $results['order_book'] = [
                'bids' => array_map(function (
                    $bid
                ) {
                    return [
                        'price' => $bid[0],
                        'amount' => $bid[1]
                    ];
                },
                    $orderBook['bids']),
                'asks' => array_map(function (
                    $ask
                ) {
                    return [
                        'price' => $ask[0],
                        'amount' => $ask[1]
                    ];
                },
                    $orderBook['asks']),
                'timestamp' => $orderBook['timestamp']
            ];
        } catch (\Exception $e) {
            $results['order_book'] = [
                'error' => $e->getMessage()
            ];
        }

        return response()->json(
            [
                'exchange' => 'KuCoin',
                'pair' => $pair,
                'results' => $results
            ]
        );
    }
}
