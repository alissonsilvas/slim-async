<?php
namespace App\Infrastructure\Mongo;

use Swoole\Coroutine\Mongo\Client;

class MongoConnection {
    private Client $client;

    public function __construct()
    {
        $host = getenv('MONGO_HOST') ?: '127.0.0.1';
        $port = getenv('MONGO_PORT') ?: '27017';
        $db   = getenv('MONGO_DB')   ?: 'meubanco';

        $this->client = new Client;
        $this->client->connect([
            'host' => $host,
            'port' => (int)$port,
            'database' => $db,
        ]);
    }

    public function collection(string $name)
    {
        return $this->client->selectCollection(
            getenv('MONGO_DB') ?: 'meubanco',
            $name
        );
    }
}
