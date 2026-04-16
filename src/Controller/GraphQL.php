<?php

declare(strict_types=1);

namespace App\Controller;

use App\GraphQL\Schema;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL as GraphQLBase;
use JsonException;
use RuntimeException;
use Throwable;

class GraphQL
{
    private static ?Schema $schema = null;

    public static function setSchema(Schema $schema): void
    {
        self::$schema = $schema;
    }

    public static function handle(): string
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            FormattedError::setInternalErrorMessage('Internal Server Error');

            $schema = self::$schema ?? throw new RuntimeException('Schema not initialized.');

            $rawInput = file_get_contents('php://input');

            if ($rawInput === false) {
                throw new RuntimeException('Unable to read request body.');
            }

            $input = self::decodeRequestBody($rawInput);
            $query = $input['query'] ?? null;

            if (!is_string($query) || trim($query) === '') {
                throw new RuntimeException('Missing GraphQL query.');
            }

            $variableValues = $input['variables'] ?? null;
            $operationName = $input['operationName'] ?? null;

            $result = GraphQLBase::executeQuery(
                $schema->build(),
                $query,
                null,
                null,
                is_array($variableValues) ? $variableValues : null,
                is_string($operationName) ? $operationName : null
            );

            return json_encode($result->toArray(), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (Throwable $throwable) {
            http_response_code(500);

            return json_encode([
                'error' => [
                    'message' => 'Internal Server Error',
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeRequestBody(string $rawInput): array
    {
        if (trim($rawInput) === '') {
            return [];
        }

        try {
            $decoded = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid JSON payload.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid GraphQL payload.');
        }

        return $decoded;
    }
}
