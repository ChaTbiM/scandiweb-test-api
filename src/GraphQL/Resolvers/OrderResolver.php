<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use GraphQL\Error\UserError;
use JsonException;
use RuntimeException;

class OrderResolver extends AbstractResolver
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function resolve(array $items): int
    {
        if ($items === []) {
            throw new UserError('Order items are required.');
        }

        $productRepository = new ProductRepository($this->pdo);

        foreach ($items as $orderItemInput) {
            $this->validateItem($orderItemInput, $productRepository);
        }

        $orderRepository = new OrderRepository($this->pdo);

        try {
            return $orderRepository->createOrder($items);
        } catch (RuntimeException $runtimeException) {
            throw new UserError($runtimeException->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $orderItemInput
     */
    private function validateItem(array $orderItemInput, ProductRepository $productRepository): void
    {
        $productId = (string) ($orderItemInput['productId'] ?? '');
        $quantity = (int) ($orderItemInput['quantity'] ?? 0);

        if ($productId === '') {
            throw new UserError('Each order item must include a productId.');
        }

        if ($quantity <= 0) {
            throw new UserError('Each order item quantity must be greater than zero.');
        }

        try {
            $productRepository->findById($productId);
        } catch (RuntimeException) {
            throw new UserError(sprintf('Product "%s" does not exist.', $productId));
        }

        $selectedAttributes = $orderItemInput['selectedAttributes'] ?? null;

        if ($selectedAttributes === null || $selectedAttributes === '') {
            return;
        }

        if (!is_string($selectedAttributes)) {
            throw new UserError('selectedAttributes must be a JSON-encoded string when provided.');
        }

        try {
            $decodedSelectedAttributes = json_decode($selectedAttributes, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new UserError('selectedAttributes must contain valid JSON.');
        }

        if (!is_array($decodedSelectedAttributes)) {
            throw new UserError('selectedAttributes must decode to a JSON object or array.');
        }
    }
}
