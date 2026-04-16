<?php

declare(strict_types=1);

namespace App;

use App\Database\Connection;
use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\OrderResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Schema;
use App\GraphQL\Types\TypeRegistry;
use App\Repository\AttributeRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\OrderItemValidator;

final class Bootstrap
{
    public static function buildSchema(): Schema
    {
        $pdo = Connection::getInstance();

        $productRepository = new ProductRepository($pdo);
        $categoryRepository = new CategoryRepository($pdo);
        $attributeRepository = new AttributeRepository($pdo);
        $orderRepository = new OrderRepository($pdo);

        $orderItemValidator = new OrderItemValidator($productRepository);

        $categoryResolver = new CategoryResolver($categoryRepository);
        $productResolver = new ProductResolver($productRepository);
        $orderResolver = new OrderResolver($orderRepository, $orderItemValidator);

        $typeRegistry = new TypeRegistry($productRepository, $attributeRepository);

        return new Schema($categoryResolver, $productResolver, $orderResolver, $typeRegistry);
    }
}
