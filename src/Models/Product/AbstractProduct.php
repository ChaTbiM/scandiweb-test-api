<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Attribute\AbstractAttribute;
use App\Models\Price;

abstract class AbstractProduct
{
    /**
     * @var array<string, class-string<self>>
     */
    private const TYPE_MAP = [
        'simple' => SimpleProduct::class,
        'configurable' => ConfigurableProduct::class,
    ];

    protected string $id;
    protected string $name;
    protected bool $inStock;
    protected string $description;
    protected string $brand;
    protected string $categoryName;

    /**
     * @var array<int, string>
     */
    protected array $gallery;

    /**
     * @var array<int, Price>
     */
    protected array $prices;

    /**
     * @var array<int, AbstractAttribute>
     */
    protected array $attributes;

    /**
     * @param array<int, string> $gallery
     * @param array<int, Price> $prices
     * @param array<int, AbstractAttribute> $attributes
     */
    public function __construct(
        string $id,
        string $name,
        bool $inStock,
        string $description,
        string $brand,
        string $categoryName,
        array $gallery = [],
        array $prices = [],
        array $attributes = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->inStock = $inStock;
        $this->description = $description;
        $this->brand = $brand;
        $this->categoryName = $categoryName;
        $this->gallery = $gallery;
        $this->prices = $prices;
        $this->attributes = $attributes;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $gallery
     * @param array<int, Price> $prices
     * @param array<int, AbstractAttribute> $attributes
     */
    public static function fromRow(
        array $row,
        array $gallery = [],
        array $prices = [],
        array $attributes = []
    ): self {
        $type = is_string($row['type'] ?? null) ? strtolower($row['type']) : 'simple';
        $productClass = self::TYPE_MAP[$type] ?? self::TYPE_MAP['simple'];

        return new $productClass(
            (string) ($row['id'] ?? ''),
            (string) ($row['name'] ?? ''),
            (bool) ($row['in_stock'] ?? $row['inStock'] ?? false),
            (string) ($row['description'] ?? ''),
            (string) ($row['brand'] ?? ''),
            (string) ($row['category_name'] ?? $row['category'] ?? $row['categoryName'] ?? ''),
            self::normalizeGallery($gallery),
            self::normalizePrices($prices),
            self::normalizeAttributes($attributes)
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    /**
     * @return array<int, string>
     */
    public function getGallery(): array
    {
        return $this->gallery;
    }

    /**
     * @return array<int, Price>
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @return array<int, AbstractAttribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    abstract public function getType(): string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'inStock' => $this->inStock,
            'description' => $this->description,
            'brand' => $this->brand,
            'category' => $this->categoryName,
            'type' => $this->getType(),
            'gallery' => $this->gallery,
            'prices' => array_map(
                static fn (Price $price): array => $price->toArray(),
                $this->prices
            ),
            'attributes' => array_map(
                static fn (AbstractAttribute $attribute): array => $attribute->toArray(),
                $this->attributes
            ),
        ];
    }

    /**
     * @param array<int, mixed> $gallery
     * @return array<int, string>
     */
    private static function normalizeGallery(array $gallery): array
    {
        return array_values(array_map(
            static fn (mixed $image): string => (string) $image,
            $gallery
        ));
    }

    /**
     * @param array<int, mixed> $prices
     * @return array<int, Price>
     */
    private static function normalizePrices(array $prices): array
    {
        return array_values(array_map(
            static function (mixed $price): Price {
                if ($price instanceof Price) {
                    return $price;
                }

                return Price::fromRow(is_array($price) ? $price : []);
            },
            $prices
        ));
    }

    /**
     * @param array<int, mixed> $attributes
     * @return array<int, AbstractAttribute>
     */
    private static function normalizeAttributes(array $attributes): array
    {
        return array_values(array_filter(
            $attributes,
            static fn (mixed $attribute): bool => $attribute instanceof AbstractAttribute
        ));
    }
}
