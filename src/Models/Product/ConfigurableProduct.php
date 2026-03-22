<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Attribute\AbstractAttribute;

class ConfigurableProduct extends AbstractProduct
{
    /**
     * @return array<int, AbstractAttribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getType(): string
    {
        return 'configurable';
    }
}
