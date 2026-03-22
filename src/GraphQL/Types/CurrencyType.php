<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CurrencyType
{
    public static function build(): ObjectType
    {
        return new ObjectType([
            'name' => 'Currency',
            'fields' => [
                'label' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $currencyValue): string => is_array($currencyValue)
                        ? (string) ($currencyValue['label'] ?? '')
                        : '',
                ],
                'symbol' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (mixed $currencyValue): string => is_array($currencyValue)
                        ? (string) ($currencyValue['symbol'] ?? '')
                        : '',
                ],
            ],
        ]);
    }
}
