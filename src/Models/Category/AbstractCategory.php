<?php

declare(strict_types=1);

namespace App\Models\Category;

use App\Models\Product\AbstractProduct;

abstract class AbstractCategory
{
    public function __construct(protected readonly int $id, protected readonly string $name)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, AbstractProduct>
     */
    abstract public function getProducts(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
