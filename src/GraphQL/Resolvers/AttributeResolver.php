<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Attribute\AbstractAttribute;
use App\Repository\AttributeRepository;

class AttributeResolver extends AbstractResolver
{
    /**
     * @return array<int, AbstractAttribute>
     */
    public function resolve(string $productId): array
    {
        $attributeRepository = new AttributeRepository($this->pdo);

        return $attributeRepository->findByProductId($productId);
    }
}
