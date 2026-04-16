<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Category;
use App\Repository\Contracts\CategoryRepositoryInterface;

class CategoryResolver
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * @return array<int, Category>
     */
    public function resolve(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function resolveByName(string $name): ?Category
    {
        return $this->categoryRepository->findByName($name);
    }
}
