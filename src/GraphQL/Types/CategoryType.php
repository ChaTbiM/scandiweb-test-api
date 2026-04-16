<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Category;
use App\Repository\Contracts\ProductRepositoryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CategoryType
{
    public static function build(TypeRegistry $registry, ProductRepositoryInterface $productRepository): ObjectType
    {
        return new ObjectType([
            'name' => 'Category',
            'fields' => static function () use ($registry, $productRepository): array {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::id()),
                        'resolve' => static fn (mixed $categoryValue): int => $categoryValue instanceof Category
                            ? $categoryValue->getId()
                            : (int) (is_array($categoryValue) ? ($categoryValue['id'] ?? 0) : 0),
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $categoryValue): string => $categoryValue instanceof Category
                            ? $categoryValue->getName()
                            : (string) (is_array($categoryValue) ? ($categoryValue['name'] ?? '') : ''),
                    ],
                    'products' => [
                        'type' => Type::nonNull(Type::listOf(Type::nonNull($registry->product()))),
                        'resolve' => static function (mixed $categoryValue) use ($productRepository): array {
                            $name = $categoryValue instanceof Category
                                ? $categoryValue->getName()
                                : (string) (is_array($categoryValue) ? ($categoryValue['name'] ?? '') : '');

                            if ($name === '') {
                                return [];
                            }

                            return $productRepository->findAllByCategory($name);
                        },
                    ],
                ];
            },
        ]);
    }
}
