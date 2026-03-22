<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Category\AbstractCategory;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CategoryType
{
    public static function build(): ObjectType
    {
        return new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (mixed $categoryValue): int => $categoryValue instanceof AbstractCategory
                        ? $categoryValue->getId()
                        : (int) (is_array($categoryValue) ? ($categoryValue['id'] ?? 0) : 0),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $categoryValue): string => $categoryValue instanceof AbstractCategory
                        ? $categoryValue->getName()
                        : (string) (is_array($categoryValue) ? ($categoryValue['name'] ?? '') : ''),
                ],
                'products' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeRegistry::product()))),
                    'resolve' => static fn (mixed $categoryValue): array => $categoryValue instanceof AbstractCategory
                        ? $categoryValue->getProducts()
                        : (is_array($categoryValue) && isset($categoryValue['products']) && is_array($categoryValue['products'])
                            ? $categoryValue['products']
                            : []),
                ],
            ],
        ]);
    }
}
