<?php

declare(strict_types=1);

namespace App\Models;

// Simple data holder for category information.
// Does not extend/implement anything — YAGNI: only one category type exists.
final class Category
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
