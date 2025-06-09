<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange_id',
        'pair',
        'price',
        'volume_24h',
        'quoted_at',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'volume_24h' => 'decimal:8',
        'quoted_at' => 'datetime',
    ];

    public $timestamps = false;

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }
}
