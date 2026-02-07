<?php

namespace App\Models;

use App\Enums\{PaymentStatus, PaymentMethod};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use mkamel\Searchable\Traits\Searchable;

class Payment extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
