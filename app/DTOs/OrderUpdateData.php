<?php

namespace App\DTOs;

class OrderUpdateData
{
    public function __construct(
        public readonly ?array $items = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            items: $data['items'],
        );
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
        ];
    }


    public function hasItemsUpdate(): bool
    {
        return !empty($this->items);
    }
}
