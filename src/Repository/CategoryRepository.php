<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Category;
use App\Repository\Contracts\CategoryRepositoryInterface;
use PDO;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int, Category>
     */
    public function findAll(): array
    {
        $categoryQuery = $this->pdo->query('SELECT id, name FROM categories ORDER BY id ASC');
        $categoryRows = $categoryQuery->fetchAll();

        return array_map(
            static fn (array $categoryRow): Category => self::hydrateCategory($categoryRow),
            $categoryRows
        );
    }

    public function findByName(string $name): ?Category
    {
        $categoryQuery = $this->pdo->prepare('SELECT id, name FROM categories WHERE name = :name LIMIT 1');
        $categoryQuery->execute(['name' => $name]);
        $categoryRow = $categoryQuery->fetch();

        if (!is_array($categoryRow)) {
            return null;
        }

        return self::hydrateCategory($categoryRow);
    }

    /**
     * @param array<string, mixed> $categoryRow
     */
    private static function hydrateCategory(array $categoryRow): Category
    {
        return new Category(
            (int) ($categoryRow['id'] ?? 0),
            (string) ($categoryRow['name'] ?? '')
        );
    }
}
