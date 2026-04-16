<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Repository\Contracts\OrderRepositoryInterface;
use App\Service\OrderItemValidator;
use GraphQL\Error\UserError;
use RuntimeException;

class OrderResolver
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemValidator $validator
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function resolve(array $items): int
    {
        if ($items === []) {
            throw new UserError('Order items are required.');
        }

        foreach ($items as $orderItemInput) {
            $this->validator->validate($orderItemInput);
        }

        try {
            return $this->orderRepository->createOrder($items);
        } catch (RuntimeException $runtimeException) {
            throw new UserError($runtimeException->getMessage());
        }
    }
}
