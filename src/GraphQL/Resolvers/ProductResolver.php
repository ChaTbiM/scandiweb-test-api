<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
use App\Repository\ProductRepository;
use RuntimeException;

class ProductResolver extends AbstractResolver
{
    public function resolve(string $id): ?AbstractProduct
    {
        $productRepository = new ProductRepository($this->pdo);

        try {
            return $productRepository->findById($id);
        } catch (RuntimeException) {
            return null;
        }
    }
}
