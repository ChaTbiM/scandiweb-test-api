<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_NAME'] ?? 'scandiweb';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$fresh = in_array('--fresh', $argv, true);

try {
    $serverConnection = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $serverConnection->exec(
        sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '``', $database)
        )
    );

    $databaseConnection = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database),
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    fwrite(STDERR, 'Database connection failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$migrationFiles = glob(__DIR__ . '/*.sql');

if ($migrationFiles === false || $migrationFiles === []) {
    fwrite(STDERR, 'No migration files found.' . PHP_EOL);
    exit(1);
}

natsort($migrationFiles);

if ($fresh) {
    $tables = [
        'order_items',
        'orders',
        'prices',
        'attribute_items',
        'attribute_sets',
        'product_gallery',
        'products',
        'categories',
    ];

    try {
        $databaseConnection->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            $databaseConnection->exec(sprintf('DROP TABLE IF EXISTS `%s`', $table));
        }

        $databaseConnection->exec('SET FOREIGN_KEY_CHECKS = 1');
        fwrite(STDOUT, 'Dropped existing project tables.' . PHP_EOL);
    } catch (PDOException $exception) {
        fwrite(STDERR, 'Failed to reset schema: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

foreach ($migrationFiles as $migrationFile) {
    $sql = file_get_contents($migrationFile);

    if ($sql === false) {
        fwrite(STDERR, 'Failed to read migration: ' . basename($migrationFile) . PHP_EOL);
        exit(1);
    }

    try {
        $databaseConnection->exec($sql);
        fwrite(STDOUT, 'Applied ' . basename($migrationFile) . PHP_EOL);
    } catch (PDOException $exception) {
        fwrite(
            STDERR,
            'Migration failed for ' . basename($migrationFile) . ': ' . $exception->getMessage() . PHP_EOL
        );
        exit(1);
    }
}

fwrite(STDOUT, 'Migrations complete.' . PHP_EOL);
