<?php

declare(strict_types=1);

namespace App\Models\Order;

use JsonException;

class OrderItem
{
    private ?int $id;
    private ?int $orderId;
    private string $productId;
    private int $quantity;
    private int $unitPriceCents;
    private string $currencyLabel;
    private string $currencySymbol;

    /**
     * @var array<string, string>
     */
    private array $selectedAttributes;

    /**
     * @param array<string, string> $selectedAttributes
     */
    public function __construct(
        ?int $id,
        ?int $orderId,
        string $productId,
        int $quantity,
        int $unitPriceCents,
        string $currencyLabel,
        string $currencySymbol,
        array $selectedAttributes = []
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->unitPriceCents = $unitPriceCents;
        $this->currencyLabel = $currencyLabel;
        $this->currencySymbol = $currencySymbol;
        $this->selectedAttributes = $selectedAttributes;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            isset($row['order_id']) ? (int) $row['order_id'] : null,
            (string) ($row['product_id'] ?? $row['productId'] ?? ''),
            (int) ($row['quantity'] ?? 1),
            (int) ($row['unit_price_cents'] ?? $row['unitPriceCents'] ?? 0),
            (string) ($row['currency_label'] ?? $row['currencyLabel'] ?? ''),
            (string) ($row['currency_symbol'] ?? $row['currencySymbol'] ?? ''),
            self::decodeSelectedAttributes($row['selected_attributes'] ?? $row['selectedAttributes'] ?? [])
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function getCurrencyLabel(): string
    {
        return $this->currencyLabel;
    }

    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    /**
     * @return array<string, string>
     */
    public function getSelectedAttributes(): array
    {
        return $this->selectedAttributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->orderId,
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'unitPriceCents' => $this->unitPriceCents,
            'price' => [
                'amount' => $this->unitPriceCents / 100,
                'currency' => [
                    'label' => $this->currencyLabel,
                    'symbol' => $this->currencySymbol,
                ],
            ],
            'selectedAttributes' => $this->selectedAttributes,
        ];
    }

    /**
     * @param mixed $selectedAttributes
     * @return array<string, string>
     */
    private static function decodeSelectedAttributes(mixed $selectedAttributes): array
    {
        if (is_array($selectedAttributes)) {
            return array_map(static fn (mixed $value): string => (string) $value, $selectedAttributes);
        }

        if (!is_string($selectedAttributes) || trim($selectedAttributes) === '') {
            return [];
        }

        try {
            $decoded = json_decode($selectedAttributes, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        return array_map(static fn (mixed $value): string => (string) $value, $decoded);
    }
}
