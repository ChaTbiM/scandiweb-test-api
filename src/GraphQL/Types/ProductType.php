<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Product\AbstractProduct;
use App\Repository\Contracts\AttributeRepositoryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class ProductType
{
    public static function build(
        TypeRegistry $registry,
        AttributeRepositoryInterface $attributeRepository
    ): ObjectType {
        return new ObjectType([
            'name' => 'Product',
            'fields' => static function () use ($registry, $attributeRepository): array {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getId()
                                : (string) (is_array($productValue)
                                    ? ($productValue['id'] ?? '')
                                    : ''),
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getName()
                                : (string) (is_array($productValue)
                                    ? ($productValue['name'] ?? '')
                                    : ''),
                    ],
                    'inStock' => [
                        'type' => Type::nonNull(Type::boolean()),
                        'resolve' => static fn (mixed $productValue): bool =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->isInStock()
                                : (bool) (is_array($productValue)
                                    ? ($productValue['inStock'] ?? false)
                                    : false),
                    ],
                    'description' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getDescription()
                                : (string) (is_array($productValue)
                                    ? ($productValue['description'] ?? '')
                                    : ''),
                    ],
                    'brand' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getBrand()
                                : (string) (is_array($productValue)
                                    ? ($productValue['brand'] ?? '')
                                    : ''),
                    ],
                    'category' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getCategoryName()
                                : (string) (is_array($productValue)
                                    ? ($productValue['category']
                                        ?? $productValue['category_name']
                                        ?? '')
                                    : ''),
                    ],
                    'type' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $productValue): string =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getType()
                                : (string) (is_array($productValue)
                                    ? ($productValue['type'] ?? '')
                                    : ''),
                    ],
                    'gallery' => [
                        'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::string()))),
                        'resolve' => static fn (mixed $productValue): array =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getGallery()
                                : (is_array($productValue)
                                    && isset($productValue['gallery'])
                                    && is_array($productValue['gallery'])
                                    ? $productValue['gallery']
                                    : []),
                    ],
                    'prices' => [
                        'type' => Type::nonNull(Type::listOf(Type::nonNull($registry->price()))),
                        'resolve' => static fn (mixed $productValue): array =>
                            $productValue instanceof AbstractProduct
                                ? $productValue->getPrices()
                                : (is_array($productValue)
                                    && isset($productValue['prices'])
                                    && is_array($productValue['prices'])
                                    ? $productValue['prices']
                                    : []),
                    ],
                    'attributes' => [
                        'type' => Type::nonNull(
                            Type::listOf(Type::nonNull($registry->attributeSet()))
                        ),
                        'resolve' => static function (mixed $productValue) use ($attributeRepository): array {
                            $productId = $productValue instanceof AbstractProduct
                                ? $productValue->getId()
                                : (string) (is_array($productValue)
                                    ? ($productValue['id'] ?? '')
                                    : '');

                            if ($productId === '') {
                                return [];
                            }

                            return $attributeRepository->findByProductId($productId);
                        },
                    ],
                ];
            },
        ]);
    }
}
