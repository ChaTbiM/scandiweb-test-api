<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class AttributeItemType
{
    public static function build(): ObjectType
    {
        return new ObjectType([
            'name' => 'AttributeItem',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $attributeItemValue): string => is_array($attributeItemValue)
                        ? (string) ($attributeItemValue['id'] ?? '')
                        : '',
                ],
                'displayValue' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $attributeItemValue): string => is_array($attributeItemValue)
                        ? (string) ($attributeItemValue['displayValue'] ?? '')
                        : '',
                ],
                'value' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $attributeItemValue): string => is_array($attributeItemValue)
                        ? (string) ($attributeItemValue['value'] ?? '')
                        : '',
                ],
            ],
        ]);
    }
}
