<?php

declare(strict_types=1);

namespace App\Models\Attribute;

class TextAttribute extends AbstractAttribute
{
    public function getInputType(): string
    {
        return 'text';
    }
}
