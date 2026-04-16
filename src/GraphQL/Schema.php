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
    public function __construct(
        private readonly CategoryResolver $categoryResolver,
        private readonly ProductResolver $productResolver,
        private readonly OrderResolver $orderResolver,
        private readonly TypeRegistry $typeRegistry
    ) {
    }

    public function build(): GraphQLSchema
    {
        return new GraphQLSchema(
            (new SchemaConfig())
                ->setQuery(new ObjectType([
                    'name' => 'Query',
                    'fields' => $this->queryFields(),
                ]))
                ->setMutation(new ObjectType([
                    'name' => 'Mutation',
                    'fields' => $this->mutationFields(),
                ]))
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function queryFields(): array
    {
        return [
            'categories' => [
                'type' => Type::nonNull(Type::listOf(Type::nonNull($this->typeRegistry->category()))),
                'resolve' => fn (): array => $this->categoryResolver->resolve(),
            ],
            'category' => [
                'type' => $this->typeRegistry->category(),
                'args' => [
                    'name' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn (mixed $rootValue, array $arguments): mixed => $this->categoryResolver
                    ->resolveByName((string) ($arguments['name'] ?? '')),
            ],
            'product' => [
                'type' => $this->typeRegistry->product(),
                'args' => [
                    'id' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn (mixed $rootValue, array $arguments): mixed => $this->productResolver
                    ->resolve((string) ($arguments['id'] ?? '')),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function mutationFields(): array
    {
        return [
            'placeOrder' => [
                'type' => Type::nonNull(Type::int()),
                'args' => [
                    'items' => Type::nonNull(Type::listOf(Type::nonNull($this->typeRegistry->orderItemInput()))),
                ],
                'resolve' => fn (mixed $rootValue, array $arguments): int => $this->orderResolver
                    ->resolve(isset($arguments['items']) && is_array($arguments['items']) ? $arguments['items'] : []),
            ],
        ];
    }
}
