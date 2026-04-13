<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\OrderResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Types\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema as GraphQLSchema;
use GraphQL\Type\SchemaConfig;

final class Schema
{
    public static function build(): GraphQLSchema
    {
        return new GraphQLSchema(
            (new SchemaConfig())
                ->setQuery(new ObjectType([
                    'name' => 'Query',
                    'fields' => self::queryFields(),
                ]))
                ->setMutation(new ObjectType([
                    'name' => 'Mutation',
                    'fields' => self::mutationFields(),
                ]))
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function queryFields(): array
    {
        return [
            'categories' => [
                'type' => Type::nonNull(Type::listOf(Type::nonNull(TypeRegistry::category()))),
                'resolve' => static fn (): array => (new CategoryResolver())->resolve(),
            ],
            'category' => [
                'type' => TypeRegistry::category(),
                'args' => [
                    'name' => Type::nonNull(Type::string()),
                ],
                'resolve' => static fn (mixed $rootValue, array $arguments): mixed => (new CategoryResolver())
                    ->resolveByName((string) ($arguments['name'] ?? '')),
            ],
            'product' => [
                'type' => TypeRegistry::product(),
                'args' => [
                    'id' => Type::nonNull(Type::string()),
                ],
                'resolve' => static fn (mixed $rootValue, array $arguments): mixed => (new ProductResolver())
                    ->resolve((string) ($arguments['id'] ?? '')),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function mutationFields(): array
    {
        return [
            'placeOrder' => [
                'type' => Type::nonNull(Type::int()),
                'args' => [
                    'items' => Type::nonNull(Type::listOf(Type::nonNull(TypeRegistry::orderItemInput()))),
                ],
                'resolve' => static fn (mixed $rootValue, array $arguments): int => (new OrderResolver())
                    ->resolve(isset($arguments['items']) && is_array($arguments['items']) ? $arguments['items'] : []),
            ],
        ];
    }
}
