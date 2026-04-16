<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
use App\Repository\Contracts\ProductRepositoryInterface;
use RuntimeException;

class ProductResolver
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function resolve(string $id): ?AbstractProduct
    {
        try {
            return $this->productRepository->findById($id);
        } catch (RuntimeException) {
            return null;
        }
    }
}
