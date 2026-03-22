<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\Models\Attribute\AbstractAttribute;
use PDO;

class AttributeRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    /**
     * @return array<int, AbstractAttribute>
     */
    public function findByProductId(string $productId): array
    {
        $attributeQuery = $this->pdo->prepare(
            'SELECT
                attribute_sets.id AS id,
                attribute_sets.attribute_id,
                attribute_sets.name,
                attribute_sets.type,
                attribute_items.item_id,
                attribute_items.display_value,
                attribute_items.value
             FROM attribute_sets
             LEFT JOIN attribute_items ON attribute_items.attribute_set_id = attribute_sets.id
             WHERE attribute_sets.product_id = :productId
             ORDER BY attribute_sets.id ASC, attribute_items.id ASC'
        );
        $attributeQuery->execute(['productId' => $productId]);
        $attributeRows = $attributeQuery->fetchAll();

        if (!is_array($attributeRows) || $attributeRows === []) {
            return [];
        }

        $attributesBySetId = [];

        foreach ($attributeRows as $attributeRow) {
            $attributeSetId = (int) ($attributeRow['id'] ?? 0);

            if (!isset($attributesBySetId[$attributeSetId])) {
                $attributesBySetId[$attributeSetId] = [
                    'row' => [
                        'id' => (int) ($attributeRow['id'] ?? 0),
                        'attribute_id' => (string) ($attributeRow['attribute_id'] ?? ''),
                        'name' => (string) ($attributeRow['name'] ?? ''),
                        'type' => (string) ($attributeRow['type'] ?? 'text'),
                    ],
                    'items' => [],
                ];
            }

            if (($attributeRow['item_id'] ?? null) === null) {
                continue;
            }

            $attributesBySetId[$attributeSetId]['items'][] = [
                'item_id' => (string) ($attributeRow['item_id'] ?? ''),
                'display_value' => (string) ($attributeRow['display_value'] ?? ''),
                'value' => (string) ($attributeRow['value'] ?? ''),
            ];
        }

        return array_values(array_map(
            static fn (array $attributeGroup): AbstractAttribute => AbstractAttribute::fromRow(
                $attributeGroup['row'],
                $attributeGroup['items']
            ),
            $attributesBySetId
        ));
    }
}
