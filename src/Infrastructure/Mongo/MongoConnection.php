<?php

declare(strict_types=1);

namespace App\Infrastructure\Mongo;

use MongoDB\Client;
use MongoDB\Collection;

class MongoConnection
{
    private Client $client;
    private string $database;

    public function __construct()
    {
        $host = (string) (getenv('MONGO_HOST') ?: 'mongo');
        $port = (string) (getenv('MONGO_PORT') ?: '27017');
        $this->database = (string) (getenv('MONGO_DB') ?: 'meubanco');

        // MongoDB Client padrão funciona com Swoole via hooks de coroutine
        $uri = sprintf('mongodb://%s:%s', $host, $port);
        $this->client = new Client($uri);
    }

    public function collection(string $name): Collection
    {
        return $this->client->selectCollection(
            $this->database,
            $name
        );
    }
}
