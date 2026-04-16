# API Setup

This backend now runs against a locally installed MySQL instance and a native PHP server.

## Requirements

- PHP 8.1+
- Composer
- MySQL 5.7

## Environment

Copy `.env.example` to `.env` if needed. The local defaults are already configured for this project:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=scandiweb
DB_USER=root
DB_PASS=password
ALLOWED_ORIGIN=http://localhost:5173
```

## Database setup

Create the database if it does not already exist:

```sql
CREATE DATABASE IF NOT EXISTS scandiweb
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Run the migrations:

```bash
composer migrate
```

If you already have an older local schema, reset the project tables first:

```bash
composer migrate:fresh
```

Seed the catalog data from `../data.json`:

```bash
composer seed
```

## Run the API

Start the local PHP server:

```bash
composer serve
```

The GraphQL endpoint will be available at `http://localhost:8080/graphql`.

## Autoload check

```bash
composer dump-autoload -o
php -r "require 'vendor/autoload.php'; echo file_exists('vendor/autoload.php') ? 'autoload.php OK' : 'autoload.php MISSING'; echo PHP_EOL; echo class_exists('GraphQL\\GraphQL') ? 'Vendor autoload OK' : 'Vendor autoload FAIL'; echo PHP_EOL; echo class_exists('App\\Controller\\GraphQL') ? 'App PSR-4 OK' : 'App PSR-4 FAIL'; echo PHP_EOL;"
```
### Registry Pattern ( needs revisit)

why ?

- its needed to avoid the circular dependencies by instantiating the class/type only once
- improves GraphQl performance ==> 'If your schema has 50 fields that reference the Product type, you still only have one instance of ProductType in memory.'

Registry Pattern is applied through TypeRegistry.php
