<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\Models\Price;
use App\Models\Product\AbstractProduct;
use PDO;
use RuntimeException;

class ProductRepository
{
    private readonly PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    /**
     * @return array<int, AbstractProduct>
     */
    public function findAllByCategory(string $categoryName): array
    {
        if ($categoryName === 'all') {
            $productQuery = $this->pdo->query(
                'SELECT
                    products.id,
                    products.name,
                    products.in_stock,
                    products.description,
                    products.category_id,
                    products.brand,
                    products.type,
                    categories.name AS category_name
                 FROM products
                 INNER JOIN categories ON categories.id = products.category_id
                 ORDER BY products.id ASC'
            );
        } else {
            $productQuery = $this->pdo->prepare(
                'SELECT
                    products.id,
                    products.name,
                    products.in_stock,
                    products.description,
                    products.category_id,
                    products.brand,
                    products.type,
                    categories.name AS category_name
                 FROM products
                 INNER JOIN categories ON categories.id = products.category_id
                 WHERE categories.name = :categoryName
                 ORDER BY products.id ASC'
            );
            $productQuery->execute(['categoryName' => $categoryName]);
        }

        $productRows = $productQuery->fetchAll();

        if (!is_array($productRows) || $productRows === []) {
            return [];
        }

        $productIds = array_values(array_map(
            static fn (array $productRow): string => (string) ($productRow['id'] ?? ''),
            $productRows
        ));

        $galleryByProductId = $this->fetchGalleryMap($productIds);
        $pricesByProductId = $this->fetchPriceMap($productIds);

        return array_map(
            static fn (array $productRow): AbstractProduct => AbstractProduct::fromRow(
                $productRow,
                $galleryByProductId[(string) ($productRow['id'] ?? '')] ?? [],
                $pricesByProductId[(string) ($productRow['id'] ?? '')] ?? []
            ),
            $productRows
        );
    }

    public function findById(string $id): AbstractProduct
    {
        $productQuery = $this->pdo->prepare(
            'SELECT
                products.id,
                products.name,
                products.in_stock,
                products.description,
                products.category_id,
                products.brand,
                products.type,
                categories.name AS category_name
             FROM products
             INNER JOIN categories ON categories.id = products.category_id
             WHERE products.id = :id
             LIMIT 1'
        );
        $productQuery->execute(['id' => $id]);
        $productRow = $productQuery->fetch();

        if (!is_array($productRow)) {
            throw new RuntimeException('Product not found.');
        }

        return AbstractProduct::fromRow(
            $productRow,
            $this->fetchGalleryMap([$id])[$id] ?? [],
            $this->fetchPriceMap([$id])[$id] ?? []
        );
    }

    /**
     * @param array<int, string> $productIds
     * @return array<string, array<int, string>>
     */
    private function fetchGalleryMap(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($productIds), '?'));
        $galleryQuery = $this->pdo->prepare(
            sprintf(
                'SELECT product_gallery.product_id, product_gallery.image_url
                 FROM product_gallery
                 WHERE product_gallery.product_id IN (%s)
                 ORDER BY product_gallery.product_id ASC, product_gallery.sort_order ASC, product_gallery.id ASC',
                $placeholders
            )
        );
        $galleryQuery->execute($productIds);

        $galleryByProductId = [];

        foreach ($galleryQuery->fetchAll() as $galleryRow) {
            $productId = (string) ($galleryRow['product_id'] ?? '');
            $galleryByProductId[$productId] ??= [];
            $galleryByProductId[$productId][] = (string) ($galleryRow['image_url'] ?? '');
        }

        return $galleryByProductId;
    }

    /**
     * @param array<int, string> $productIds
     * @return array<string, array<int, Price>>
     */
    private function fetchPriceMap(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($productIds), '?'));
        $priceQuery = $this->pdo->prepare(
            sprintf(
                'SELECT prices.product_id, prices.amount, prices.currency_label, prices.currency_symbol
                 FROM prices
                 WHERE prices.product_id IN (%s)
                 ORDER BY prices.product_id ASC, prices.id ASC',
                $placeholders
            )
        );
        $priceQuery->execute($productIds);

        $pricesByProductId = [];

        foreach ($priceQuery->fetchAll() as $priceRow) {
            $productId = (string) ($priceRow['product_id'] ?? '');
            $pricesByProductId[$productId] ??= [];
            $pricesByProductId[$productId][] = Price::fromRow(is_array($priceRow) ? $priceRow : []);
        }

        return $pricesByProductId;
    }
}
