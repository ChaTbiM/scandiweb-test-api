<?php

declare(strict_types=1);

namespace App\Models\Order;

use DateTimeImmutable;

class Order
{
    /**
     * @param array<int, OrderItem> $items
     */
    public function __construct(
        private readonly ?int $id,
        private readonly ?DateTimeImmutable $createdAt = null,
        private readonly array $items = []
    ) {
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, OrderItem> $items
     */
    public static function fromRow(array $row, array $items = []): self
    {
        $createdAt = null;

        if (isset($row['created_at']) && is_string($row['created_at']) && $row['created_at'] !== '') {
            $createdAt = new DateTimeImmutable($row['created_at']);
        }

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            $createdAt,
            $items
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<int, OrderItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'createdAt' => $this->createdAt?->format(DATE_ATOM),
            'items' => array_map(
                static fn (OrderItem $item): array => $item->toArray(),
                $this->items
            ),
        ];
    }
}
