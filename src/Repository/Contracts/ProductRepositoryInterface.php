<?php

declare(strict_types=1);

namespace App\Repository\Contracts;

use App\Models\Product\AbstractProduct;

interface ProductRepositoryInterface
{
    /**
     * @return array<int, AbstractProduct>
     */
    public function findAllByCategory(string $categoryName): array;

    public function findById(string $id): AbstractProduct;
}
