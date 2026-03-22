<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class OrderItemInputType
{
    public static function build(): InputObjectType
    {
        return new InputObjectType([
            'name' => 'OrderItemInput',
            'fields' => [
                'productId' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'quantity' => [
                    'type' => Type::nonNull(Type::int()),
                ],
                'selectedAttributes' => [
                    'type' => Type::string(),
                ],
            ],
        ]);
    }
}
