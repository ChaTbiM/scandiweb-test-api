<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Category\AbstractCategory;
use App\Repository\CategoryRepository;

class CategoryResolver extends AbstractResolver
{
    /**
     * @return array<int, AbstractCategory>
     */
    public function resolve(): array
    {
        $categoryRepository = new CategoryRepository($this->pdo);

        return $categoryRepository->findAll();
    }

    public function resolveByName(string $name): ?AbstractCategory
    {
        $categoryRepository = new CategoryRepository($this->pdo);

        return $categoryRepository->findByName($name);
    }
}
