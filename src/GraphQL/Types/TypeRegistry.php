<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;

final class TypeRegistry
{
    private static ?ObjectType $currencyType = null;
    private static ?ObjectType $priceType = null;
    private static ?ObjectType $attributeItemType = null;
    private static ?ObjectType $attributeSetType = null;
    private static ?ObjectType $productType = null;
    private static ?ObjectType $categoryType = null;
    private static ?InputObjectType $orderItemInputType = null;

    public static function currency(): ObjectType
    {
        return self::$currencyType ??= CurrencyType::build();
    }

    public static function price(): ObjectType
    {
        return self::$priceType ??= PriceType::build();
    }

    public static function attributeItem(): ObjectType
    {
        return self::$attributeItemType ??= AttributeItemType::build();
    }

    public static function attributeSet(): ObjectType
    {
        return self::$attributeSetType ??= AttributeSetType::build();
    }

    public static function product(): ObjectType
    {
        return self::$productType ??= ProductType::build();
    }

    public static function category(): ObjectType
    {
        return self::$categoryType ??= CategoryType::build();
    }

    public static function orderItemInput(): InputObjectType
    {
        return self::$orderItemInputType ??= OrderItemInputType::build();
    }
}
