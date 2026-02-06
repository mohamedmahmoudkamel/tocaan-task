<?php

namespace App\Models;

use App\Enums\{PaymentStatus, PaymentMethod};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'amount',
        'metadata',
        'gateway_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
