<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Price;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class PriceType
{
    public static function build(): ObjectType
    {
        return new ObjectType([
            'name' => 'Price',
            'fields' => [
                'amount' => [
                    'type' => Type::nonNull(Type::float()),
                    'resolve' => static fn (mixed $priceValue): float => $priceValue instanceof Price
                        ? $priceValue->getAmount()
                        : (float) (is_array($priceValue) ? ($priceValue['amount'] ?? 0) : 0),
                ],
                'currency' => [
                    'type' => Type::nonNull(TypeRegistry::currency()),
                    'resolve' => static function (mixed $priceValue): array {
                        if ($priceValue instanceof Price) {
                            return [
                                'label' => $priceValue->getCurrencyLabel(),
                                'symbol' => $priceValue->getCurrencySymbol(),
                            ];
                        }

                        return is_array($priceValue)
                            && isset($priceValue['currency'])
                            && is_array($priceValue['currency'])
                            ? $priceValue['currency']
                            : ['label' => '', 'symbol' => ''];
                    },
                ],
            ],
        ]);
    }
}
