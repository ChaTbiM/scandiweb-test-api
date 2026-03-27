<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\Models\Order\OrderItem;
use JsonException;
use PDO;
use RuntimeException;
use Throwable;

class OrderRepository
{
    private readonly PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    /**
     * @param array<int, array<string, mixed>|OrderItem> $items
     */
    public function createOrder(array $items): int
    {
        if ($items === []) {
            throw new RuntimeException('Cannot create order without items.');
        }

        $normalizedOrderItems = $this->normalizeItems($items);

        if ($normalizedOrderItems === []) {
            throw new RuntimeException('Cannot create order without items.');
        }

        $insertOrderStatement = $this->pdo->prepare('INSERT INTO orders (created_at) VALUES (CURRENT_TIMESTAMP)');
        $insertOrderItemStatement = $this->pdo->prepare(
            'INSERT INTO order_items (
                order_id,
                product_id,
                quantity,
                unit_price_cents,
                currency_label,
                currency_symbol,
                selected_attributes
            ) VALUES (
                :orderId,
                :productId,
                :quantity,
                :unitPriceCents,
                :currencyLabel,
                :currencySymbol,
                :selectedAttributes
            )'
        );

        try {
            $this->pdo->beginTransaction();
            $insertOrderStatement->execute();
            $orderId = (int) $this->pdo->lastInsertId();

            foreach ($normalizedOrderItems as $normalizedOrderItem) {
                $priceSnapshot = $this->fetchPriceSnapshot($normalizedOrderItem['productId']);
                $insertOrderItemStatement->execute([
                    'orderId' => $orderId,
                    'productId' => $normalizedOrderItem['productId'],
                    'quantity' => $normalizedOrderItem['quantity'],
                    'unitPriceCents' => $priceSnapshot['amount'],
                    'currencyLabel' => $priceSnapshot['currency_label'],
                    'currencySymbol' => $priceSnapshot['currency_symbol'],
                    'selectedAttributes' => $this->encodeSelectedAttributes($normalizedOrderItem['selectedAttributes']),
                ]);
            }

            $this->pdo->commit();

            return $orderId;
        } catch (Throwable $throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param array<int, array<string, mixed>|OrderItem> $items
     * @return array<int, array{productId: string, quantity: int, selectedAttributes: array<string, string>}>
     */
    private function normalizeItems(array $items): array
    {
        $normalizedOrderItems = [];

        foreach ($items as $orderItemInput) {
            if ($orderItemInput instanceof OrderItem) {
                $normalizedOrderItems[] = [
                    'productId' => $orderItemInput->getProductId(),
                    'quantity' => $orderItemInput->getQuantity(),
                    'selectedAttributes' => $orderItemInput->getSelectedAttributes(),
                ];

                continue;
            }

            if (!is_array($orderItemInput)) {
                throw new RuntimeException('Each order item must be an array or OrderItem instance.');
            }

            $productId = (string) ($orderItemInput['productId'] ?? $orderItemInput['product_id'] ?? '');
            $quantity = (int) ($orderItemInput['quantity'] ?? 0);

            if ($productId === '' || $quantity <= 0) {
                throw new RuntimeException('Each order item must include a valid productId and quantity.');
            }

            $normalizedOrderItems[] = [
                'productId' => $productId,
                'quantity' => $quantity,
                'selectedAttributes' => $this->normalizeSelectedAttributes(
                    $orderItemInput['selectedAttributes'] ?? $orderItemInput['selected_attributes'] ?? []
                ),
            ];
        }

        return $normalizedOrderItems;
    }

    /**
     * @return array{amount: int, currency_label: string, currency_symbol: string}
     */
    private function fetchPriceSnapshot(string $productId): array
    {
        $priceSnapshotQuery = $this->pdo->prepare(
            'SELECT amount, currency_label, currency_symbol
             FROM prices
             WHERE product_id = :productId
             ORDER BY id ASC
             LIMIT 1'
        );
        $priceSnapshotQuery->execute(['productId' => $productId]);
        $priceSnapshotRow = $priceSnapshotQuery->fetch();

        if (!is_array($priceSnapshotRow)) {
            throw new RuntimeException(sprintf('Unable to snapshot price for product "%s".', $productId));
        }

        return [
            'amount' => (int) ($priceSnapshotRow['amount'] ?? 0),
            'currency_label' => (string) ($priceSnapshotRow['currency_label'] ?? ''),
            'currency_symbol' => (string) ($priceSnapshotRow['currency_symbol'] ?? ''),
        ];
    }

    /**
     * @param mixed $selectedAttributes
     * @return array<string, string>
     */
    private function normalizeSelectedAttributes(mixed $selectedAttributes): array
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

    /**
     * @param array<string, string> $selectedAttributes
     */
    private function encodeSelectedAttributes(array $selectedAttributes): ?string
    {
        if ($selectedAttributes === []) {
            return null;
        }

        try {
            return json_encode($selectedAttributes, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Unable to encode selected attributes.');
        }
    }
}
