<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\Client;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MongoConnectionUnitTest extends TestCase
{
    private MongoConnection $mongoConnection;
    private MockObject $mockClient;
    private MockObject $mockCollection;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->mockCollection = $this->createMock(Collection::class);
        
        // Cria uma instância da classe usando reflexão para injetar o mock
        $this->mongoConnection = $this->createMongoConnectionWithMockClient();
    }

    private function createMongoConnectionWithMockClient(): MongoConnection
    {
        $reflection = new \ReflectionClass(MongoConnection::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        // Injeta o mock client usando reflexão
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($instance, $this->mockClient);
        
        // Injeta o database name
        $databaseProperty = $reflection->getProperty('database');
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue($instance, 'test_db');
        
        return $instance;
    }

    public function testCollectionMethodReturnsCollection(): void
    {
        $collectionName = 'test_collection';
        
        $this->mockClient
            ->expects($this->once())
            ->method('selectCollection')
            ->with('test_db', $collectionName)
            ->willReturn($this->mockCollection);
        
        $result = $this->mongoConnection->collection($collectionName);
        
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testCollectionMethodCallsClientWithCorrectParameters(): void
    {
        $collectionName = 'users';
        
        $this->mockClient
            ->expects($this->once())
            ->method('selectCollection')
            ->with('test_db', $collectionName)
            ->willReturn($this->mockCollection);
        
        $this->mongoConnection->collection($collectionName);
    }

    public function testCanHandleMultipleCollectionCalls(): void
    {
        $collection1 = 'users';
        $collection2 = 'products';
        
        $this->mockClient
            ->expects($this->exactly(2))
            ->method('selectCollection')
            ->willReturnCallback(function($database, $collection) {
                $this->assertEquals('test_db', $database);
                $this->assertContains($collection, ['users', 'products']);
                return $this->mockCollection;
            });
        
        $this->mongoConnection->collection($collection1);
        $this->mongoConnection->collection($collection2);
    }
}
