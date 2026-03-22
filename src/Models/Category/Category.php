<?php

declare(strict_types=1);

namespace App\Models\Category;

use App\Models\Product\AbstractProduct;

class Category extends AbstractCategory
{
    /**
     * @var array<int, AbstractProduct>|null
     */
    private ?array $products = null;

    /**
     * @return array<int, AbstractProduct>
     */
    public function getProducts(): array
    {
        if ($this->products !== null) {
            return $this->products;
        }

        $repositoryClass = 'App\\Repository\\ProductRepository';

        if (!class_exists($repositoryClass)) {
            $this->products = [];

            return $this->products;
        }

        $repository = new $repositoryClass();

        if (!method_exists($repository, 'findAllByCategory')) {
            $this->products = [];

            return $this->products;
        }

        $products = $repository->findAllByCategory($this->name);
        $this->products = is_array($products) ? $products : [];

        return $this->products;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray() + [
            'products' => array_map(
                static fn (AbstractProduct $product): array => $product->toArray(),
                $this->getProducts()
            ),
        ];
    }
}
