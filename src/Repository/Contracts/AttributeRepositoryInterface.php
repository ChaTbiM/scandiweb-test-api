<?php

declare(strict_types=1);

namespace App\Repository\Contracts;

use App\Models\Attribute\AbstractAttribute;

interface AttributeRepositoryInterface
{
    /**
     * @return array<int, AbstractAttribute>
     */
    public function findByProductId(string $productId): array;
}
