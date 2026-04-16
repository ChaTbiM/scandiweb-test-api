<?php

declare(strict_types=1);

namespace App\Repository\Contracts;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return array<int, Category>
     */
    public function findAll(): array;

    public function findByName(string $name): ?Category;
}
