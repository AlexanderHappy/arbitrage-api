<?php

namespace App\Console\Commands;

use App\Models\Exchange;
use App\Services\Exchange\ExchangeService;
use Illuminate\Console\Command;

class TestKuCoinConnection extends Command
{
    protected $signature = 'kucoin:test {pair=BTC-USDT}';
    protected $description = 'Test KuCoin API connection';

    public function handle(): int
    {
        $pair = $this->argument('pair');

        dd(
            $pair
        );

        // Get or create KuCoin exchange
        $exchange = Exchange::firstOrCreate(
            ['name' => 'KuCoin'],
            [
                'api_url' => 'https://api.kucoin.com',
                'is_active' => true,
                'rate_limit' => 1800,
                'trading_fee' => 0.001,
                'withdrawal_fee' => 0.0005,
                'min_trade_amount' => 10,
                'supported_pairs' => ['BTC/USDT', 'ETH/USDT', 'SOL/USDT']
            ]
        );


        $service = new ExchangeService($exchange);

        $this->info("Testing KuCoin API for {$pair}...\n");

        // Test health check
        $this->info("1. Health Check:");
        try {
            $health = $service->healthCheck();
            $this->line($health ? "✅ Exchange is online" : "❌ Exchange is offline");
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }

        // Test price data
        $this->info("\n2. Price Data:");
        try {
            $price = $service->getPrice($pair);
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Pair', $price->pair],
                    ['Bid', $price->bid],
                    ['Ask', $price->ask],
                    ['Last', $price->last],
                    ['Spread', $price->getSpread()],
                    ['Spread %', number_format($price->getSpreadPercentage(), 4) . '%'],
                    ['24h Volume', number_format($price->volume24h, 2)],
                ]
            );
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }

        // Test order book
        $this->info("\n3. Order Book (top 5):");
        try {
            $orderBook = $service->getOrderBook($pair, 5);

            $this->info("Bids:");
            foreach ($orderBook['bids'] as $bid) {
                $this->line("  Price: {$bid[0]}, Amount: {$bid[1]}");
            }

            $this->info("\nAsks:");
            foreach ($orderBook['asks'] as $ask) {
                $this->line("  Price: {$ask[0]}, Amount: {$ask[1]}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
