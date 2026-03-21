<?php

declare(strict_types=1);

namespace App\GraphQL;

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
            '_placeholder' => [
                'type' => Type::string(),
                'resolve' => static fn (): string => 'Schema not fully wired yet.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function mutationFields(): array
    {
        return [
            '_placeholder' => [
                'type' => Type::string(),
                'resolve' => static fn (): string => 'Schema not fully wired yet.',
            ],
        ];
    }
}
