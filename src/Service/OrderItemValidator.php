<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\Contracts\ProductRepositoryInterface;
use GraphQL\Error\UserError;
use JsonException;
use RuntimeException;

final class OrderItemValidator
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @param array<string, mixed> $orderItemInput
     */
    public function validate(array $orderItemInput): void
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
            $this->productRepository->findById($productId);
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
