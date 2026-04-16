<?php

declare(strict_types=1);

namespace App\Repository\Contracts;

interface OrderRepositoryInterface
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function createOrder(array $items): int;
}
