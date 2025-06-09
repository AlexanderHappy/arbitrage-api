<?php

namespace App\Interfaces;

use App\Dto\DtoPriceData;

interface InterfaceExchangeStrategy
{
    /**
     * Get current price for trading pair
     */
    public function getPrice(string $pair): DtoPriceData;

    /**
     * Get order book depth
     */
    public function getOrderBook(string $pair, int $limit = 10): array;

    /**
     * Get 24h volume
     */
    public function get24hVolume(string $pair): float;

    /**
     * Check if exchange is available
     */
    public function healthCheck(): bool;

    /**
     * Get exchange name
     */
    public function getName(): string;
}
