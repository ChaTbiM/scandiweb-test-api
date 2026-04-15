<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\GraphQL\Resolvers\AttributeResolver;
use App\Models\Product\AbstractProduct;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class ProductType
{
    public static function build(): ObjectType
    {
        return new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getId()
                        : (string) (is_array($productValue) ? ($productValue['id'] ?? '') : ''),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getName()
                        : (string) (is_array($productValue) ? ($productValue['name'] ?? '') : ''),
                ],
                'inStock' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'resolve' => static fn (mixed $productValue): bool => $productValue instanceof AbstractProduct
                        ? $productValue->isInStock()
                        : (bool) (is_array($productValue) ? ($productValue['inStock'] ?? false) : false),
                ],
                'description' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getDescription()
                        : (string) (is_array($productValue) ? ($productValue['description'] ?? '') : ''),
                ],
                'brand' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getBrand()
                        : (string) (is_array($productValue) ? ($productValue['brand'] ?? '') : ''),
                ],
                'category' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getCategoryName()
                        : (string) (is_array($productValue)
                            ? ($productValue['category'] ?? $productValue['category_name'] ?? '')
                            : ''),
                ],
                'type' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $productValue): string => $productValue instanceof AbstractProduct
                        ? $productValue->getType()
                        : (string) (is_array($productValue) ? ($productValue['type'] ?? '') : ''),
                ],
                'gallery' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::string()))),
                    'resolve' => static fn (mixed $productValue): array => $productValue instanceof AbstractProduct
                        ? $productValue->getGallery()
                        : (is_array($productValue)
                            && isset($productValue['gallery'])
                            && is_array($productValue['gallery'])
                            ? $productValue['gallery']
                            : []),
                ],
                'prices' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeRegistry::price()))),
                    'resolve' => static fn (mixed $productValue): array => $productValue instanceof AbstractProduct
                        ? $productValue->getPrices()
                        : (is_array($productValue)
                            && isset($productValue['prices'])
                            && is_array($productValue['prices'])
                            ? $productValue['prices']
                            : []),
                ],
                'attributes' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeRegistry::attributeSet()))),
                    'resolve' => static function (mixed $productValue): array {
                        $productId = $productValue instanceof AbstractProduct
                            ? $productValue->getId()
                            : (string) (is_array($productValue) ? ($productValue['id'] ?? '') : '');

                        if ($productId === '') {
                            return [];
                        }

                        return (new AttributeResolver())->resolve($productId);
                    },
                ],
            ],
        ]);
    }
}
