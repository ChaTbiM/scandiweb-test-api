<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Attribute\AbstractAttribute;
use App\Repository\Contracts\AttributeRepositoryInterface;

class AttributeResolver
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository
    ) {
    }

    /**
     * @return array<int, AbstractAttribute>
     */
    public function resolve(string $productId): array
    {
        return $this->attributeRepository->findByProductId($productId);
    }
}
