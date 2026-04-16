<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Attribute\AbstractAttribute;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class AttributeSetType
{
    public static function build(TypeRegistry $registry): ObjectType
    {
        return new ObjectType([
            'name' => 'AttributeSet',
            'fields' => static function () use ($registry): array {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $attributeValue): string =>
                            $attributeValue instanceof AbstractAttribute
                                ? $attributeValue->getAttributeId()
                                : (string) (is_array($attributeValue)
                                    ? ($attributeValue['id'] ?? '')
                                    : ''),
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $attributeValue): string =>
                            $attributeValue instanceof AbstractAttribute
                                ? $attributeValue->getName()
                                : (string) (is_array($attributeValue)
                                    ? ($attributeValue['name'] ?? '')
                                    : ''),
                    ],
                    'type' => [
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => static fn (mixed $attributeValue): string =>
                            $attributeValue instanceof AbstractAttribute
                                ? $attributeValue->getInputType()
                                : (string) (is_array($attributeValue)
                                    ? ($attributeValue['type'] ?? '')
                                    : ''),
                    ],
                    'items' => [
                        'type' => Type::nonNull(
                            Type::listOf(Type::nonNull($registry->attributeItem()))
                        ),
                        'resolve' => static fn (mixed $attributeValue): array =>
                            $attributeValue instanceof AbstractAttribute
                                ? $attributeValue->getItems()
                                : (is_array($attributeValue)
                                    && isset($attributeValue['items'])
                                    && is_array($attributeValue['items'])
                                    ? $attributeValue['items']
                                    : []),
                    ],
                ];
            },
        ]);
    }
}
