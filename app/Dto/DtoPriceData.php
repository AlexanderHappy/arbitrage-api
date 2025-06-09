<?php

namespace App\Dto;

readonly class DtoPriceData
{
    public function __construct(
        public string             $pair,
        public float              $bid,
        public float              $ask,
        public float              $last,
        public float              $volume24h,
        public string             $exchange,
        public \DateTimeInterface $quotedAt
    )
    {
    }

    public function getSpread(): float
    {
        return $this->ask - $this->bid;
    }

    public function getSpreadPercentage(): float
    {
        return ($this->getSpread() / $this->ask) * 100;
    }
}
