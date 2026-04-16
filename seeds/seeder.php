<?php

/**
 * Database Seeder
 *
 * Parses data.json and populates the database with categories,
 * products, galleries, attributes, attribute items, and prices.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env if Dotenv is available (for local development)
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'scandiweb';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Find data.json in expected locations
$dataFile = null;
$searchPaths = [
    __DIR__ . '/data.json',
    __DIR__ . '/../data.json',
    __DIR__ . '/../../data.json',
];

foreach ($searchPaths as $path) {
    if (file_exists($path)) {
        $dataFile = realpath($path);
        break;
    }
}

if (!$dataFile) {
    fwrite(STDERR, "data.json not found. Searched:\n");
    foreach ($searchPaths as $path) {
        fwrite(STDERR, "  - {$path}\n");
    }
    exit(1);
}

echo "Using data file: {$dataFile}\n";

$json = file_get_contents($dataFile);
$data = json_decode($json, true);

if (!$data || !isset($data['data'])) {
    fwrite(STDERR, "Invalid data.json format\n");
    exit(1);
}

$categories = $data['data']['categories'];
$products = $data['data']['products'];

echo "Seeding database...\n\n";

// Clear existing data
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = [
    'order_items', 'orders', 'prices',
    'attribute_items', 'attribute_sets',
    'product_gallery', 'products', 'categories',
];
foreach ($tables as $table) {
    $pdo->exec("TRUNCATE TABLE {$table}");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// --- Seed Categories ---
echo "Seeding categories...\n";
$categoryMap = [];
$categoryStmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");

foreach ($categories as $cat) {
    $categoryStmt->execute(['name' => $cat['name']]);
    $categoryMap[$cat['name']] = (int) $pdo->lastInsertId();
}
echo "  Inserted " . count($categories) . " categories\n";

// --- Seed Products ---
echo "Seeding products...\n";

$productStmt = $pdo->prepare(
    "INSERT INTO products (id, name, in_stock, description, category_id, brand, type)
     VALUES (:id, :name, :in_stock, :description, :category_id, :brand, :type)"
);

$galleryStmt = $pdo->prepare(
    "INSERT INTO product_gallery (product_id, image_url, sort_order)
     VALUES (:product_id, :image_url, :sort_order)"
);

$attrSetStmt = $pdo->prepare(
    "INSERT INTO attribute_sets (product_id, attribute_id, name, type)
     VALUES (:product_id, :attribute_id, :name, :type)"
);

$attrItemStmt = $pdo->prepare(
    "INSERT INTO attribute_items (attribute_set_id, item_id, display_value, value)
     VALUES (:attribute_set_id, :item_id, :display_value, :value)"
);

$priceStmt = $pdo->prepare(
    "INSERT INTO prices (product_id, amount, currency_label, currency_symbol)
     VALUES (:product_id, :amount, :currency_label, :currency_symbol)"
);

$galleryCount = 0;
$attrSetCount = 0;
$attrItemCount = 0;
$priceCount = 0;

foreach ($products as $product) {
    $categoryId = $categoryMap[$product['category']] ?? null;

    if ($categoryId === null) {
        $msg = "  WARNING: Category '{$product['category']}'"
            . " not found for product '{$product['id']}', skipping\n";
        fwrite(STDERR, $msg);
        continue;
    }

    $type = !empty($product['attributes']) ? 'configurable' : 'simple';

    // Insert product
    $productStmt->execute([
        'id' => $product['id'],
        'name' => $product['name'],
        'in_stock' => $product['inStock'] ? 1 : 0,
        'description' => $product['description'] ?? null,
        'category_id' => $categoryId,
        'brand' => $product['brand'] ?? null,
        'type' => $type,
    ]);

    // Insert gallery images
    foreach ($product['gallery'] as $index => $imageUrl) {
        $galleryStmt->execute([
            'product_id' => $product['id'],
            'image_url' => $imageUrl,
            'sort_order' => $index,
        ]);
        $galleryCount++;
    }

    // Insert attribute sets and their items
    if (!empty($product['attributes'])) {
        foreach ($product['attributes'] as $attrSet) {
            $attrSetStmt->execute([
                'product_id' => $product['id'],
                'attribute_id' => $attrSet['id'],
                'name' => $attrSet['name'],
                'type' => $attrSet['type'],
            ]);
            $attrSetId = (int) $pdo->lastInsertId();
            $attrSetCount++;

            foreach ($attrSet['items'] as $item) {
                $attrItemStmt->execute([
                    'attribute_set_id' => $attrSetId,
                    'item_id' => $item['id'],
                    'display_value' => $item['displayValue'],
                    'value' => $item['value'],
                ]);
                $attrItemCount++;
            }
        }
    }

    // Insert prices
    foreach ($product['prices'] as $price) {
        $priceStmt->execute([
            'product_id' => $product['id'],
            'amount' => (int) round($price['amount'] * 100),
            'currency_label' => $price['currency']['label'],
            'currency_symbol' => $price['currency']['symbol'],
        ]);
        $priceCount++;
    }

    echo "  Seeded product: {$product['name']} ({$product['id']})\n";
}

echo "\n--- Seeding Complete ---\n";
echo "Summary:\n";
echo "  Categories:       " . count($categories) . "\n";
echo "  Products:         " . count($products) . "\n";
echo "  Gallery images:   {$galleryCount}\n";
echo "  Attribute sets:   {$attrSetCount}\n";
echo "  Attribute items:  {$attrItemCount}\n";
echo "  Prices:           {$priceCount}\n";

// Verify against DB
echo "\nVerification (DB row counts):\n";
$verifyTables = ['categories', 'products', 'product_gallery', 'attribute_sets', 'attribute_items', 'prices'];
foreach ($verifyTables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    echo "  {$table}: {$count}\n";
}

echo "\nDone!\n";
