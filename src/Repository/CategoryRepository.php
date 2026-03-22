<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\Models\Category\AbstractCategory;
use App\Models\Category\Category;
use PDO;

class CategoryRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    /**
     * @return array<int, AbstractCategory>
     */
    public function findAll(): array
    {
        $categoryQuery = $this->pdo->query('SELECT id, name FROM categories ORDER BY id ASC');
        $categoryRows = $categoryQuery->fetchAll();

        return array_map(
            static fn (array $categoryRow): AbstractCategory => self::hydrateCategory($categoryRow),
            $categoryRows
        );
    }

    public function findByName(string $name): ?AbstractCategory
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
    private static function hydrateCategory(array $categoryRow): AbstractCategory
    {
        return new Category(
            (int) ($categoryRow['id'] ?? 0),
            (string) ($categoryRow['name'] ?? '')
        );
    }
}
