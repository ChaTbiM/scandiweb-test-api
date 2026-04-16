<?php

declare(strict_types=1);

use App\Bootstrap;
use App\Controller\GraphQL;
use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

require_once __DIR__ . '/../vendor/autoload.php';

$sendJsonResponse = static function (int $statusCode, array $payload, array $headers = []): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');

    foreach ($headers as $name => $value) {
        header($name . ': ' . $value);
    }

    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
};

try {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();

    GraphQL::setSchema(Bootstrap::buildSchema());

    $allowedOrigin = $_ENV['ALLOWED_ORIGIN'] ?? getenv('ALLOWED_ORIGIN') ?: 'http://localhost:5173';
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $dispatcher = FastRoute\simpleDispatcher(static function (RouteCollector $routeCollector): void {
        $routeCollector->post('/graphql', [GraphQL::class, 'handle']);
    });

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $queryStringPosition = strpos($requestUri, '?');

    if ($queryStringPosition !== false) {
        $requestUri = substr($requestUri, 0, $queryStringPosition);
    }

    $routeInfo = $dispatcher->dispatch($requestMethod, rawurldecode($requestUri));

    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $sendJsonResponse(404, [
                'error' => [
                    'message' => 'Not Found but app is working ! Hello World !',
                ],
            ]);
            break;

        case Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = implode(', ', $routeInfo[1]);

            $sendJsonResponse(
                405,
                [
                    'error' => [
                        'message' => 'Method Not Allowed',
                    ],
                ],
                ['Allow' => $allowedMethods]
            );
            break;

        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            echo call_user_func($handler);
            break;
    }
} catch (Throwable $throwable) {
    $sendJsonResponse(500, [
        'error' => [
            'message' => 'Internal Server Error: ' . $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine()
        ],
    ]);
}
