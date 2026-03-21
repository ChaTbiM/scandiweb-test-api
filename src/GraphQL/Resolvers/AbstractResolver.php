<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Database\Connection;
use PDO;

abstract class AbstractResolver
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }
}
