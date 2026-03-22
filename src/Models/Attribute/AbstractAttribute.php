<?php

declare(strict_types=1);

namespace App\Models\Attribute;

abstract class AbstractAttribute
{
    /**
     * @var array<string, class-string<self>>
     */
    private const TYPE_MAP = [
        'text' => TextAttribute::class,
        'swatch' => SwatchAttribute::class,
    ];

    protected int $id;
    protected string $attributeId;
    protected string $name;
    protected string $type;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $items;

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(int $id, string $attributeId, string $name, string $type, array $items = [])
    {
        $this->id = $id;
        $this->attributeId = $attributeId;
        $this->name = $name;
        $this->type = $type;
        $this->items = $items;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, array<string, mixed>> $items
     */
    public static function fromRow(array $row, array $items = []): self
    {
        $type = is_string($row['type'] ?? null) ? strtolower($row['type']) : 'text';
        $attributeClass = self::TYPE_MAP[$type] ?? self::TYPE_MAP['text'];

        return new $attributeClass(
            (int) ($row['id'] ?? 0),
            (string) ($row['attribute_id'] ?? $row['attributeId'] ?? $row['id'] ?? ''),
            (string) ($row['name'] ?? ''),
            $type,
            self::normalizeItems($items)
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAttributeId(): string
    {
        return $this->attributeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    abstract public function getInputType(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->attributeId,
            'dbId' => $this->id,
            'name' => $this->name,
            'type' => $this->getInputType(),
            'items' => $this->items,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeItems(array $items): array
    {
        return array_values(array_map(
            static function (array $item): array {
                return [
                    'id' => (string) ($item['item_id'] ?? $item['id'] ?? ''),
                    'displayValue' => (string) ($item['display_value'] ?? $item['displayValue'] ?? ''),
                    'value' => (string) ($item['value'] ?? ''),
                ];
            },
            $items
        ));
    }
}
