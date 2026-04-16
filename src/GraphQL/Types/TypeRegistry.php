<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Repository\Contracts\AttributeRepositoryInterface;
use App\Repository\Contracts\ProductRepositoryInterface;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;

final class TypeRegistry
{
    private ?ObjectType $currencyType = null;
    private ?ObjectType $priceType = null;
    private ?ObjectType $attributeItemType = null;
    private ?ObjectType $attributeSetType = null;
    private ?ObjectType $productType = null;
    private ?ObjectType $categoryType = null;
    private ?InputObjectType $orderItemInputType = null;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly AttributeRepositoryInterface $attributeRepository
    ) {
    }

    public function currency(): ObjectType
    {
        return $this->currencyType ??= CurrencyType::build();
    }

    public function price(): ObjectType
    {
        return $this->priceType ??= PriceType::build($this);
    }

    public function attributeItem(): ObjectType
    {
        return $this->attributeItemType ??= AttributeItemType::build();
    }

    public function attributeSet(): ObjectType
    {
        return $this->attributeSetType ??= AttributeSetType::build($this);
    }

    public function product(): ObjectType
    {
        return $this->productType ??= ProductType::build($this, $this->attributeRepository);
    }

    public function category(): ObjectType
    {
        return $this->categoryType ??= CategoryType::build($this, $this->productRepository);
    }

    public function orderItemInput(): InputObjectType
    {
        return $this->orderItemInputType ??= OrderItemInputType::build();
    }
}
