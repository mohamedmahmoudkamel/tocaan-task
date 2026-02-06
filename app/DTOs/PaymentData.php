<?php

namespace App\DTOs;

use App\Models\Order;

class PaymentData
{
    public function __construct(
        public readonly float $amount,
        public readonly string $method,
        public readonly array $metadata,
        public readonly Order $order
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            method: $data['method'],
            metadata: $data['metadata'],
            order: $data['order'],
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'method' => $this->method,
            'metadata' => $this->metadata,
            'order' => $this->order,
        ];
    }
}
