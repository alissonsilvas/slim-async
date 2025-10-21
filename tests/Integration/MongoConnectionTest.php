<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;

class MongoConnectionTest extends TestCase
{
    private MongoConnection $mongoConnection;

    protected function setUp(): void
    {
        $this->mongoConnection = new MongoConnection();
    }

    public function testCanCreateConnection(): void
    {
        $this->assertInstanceOf(MongoConnection::class, $this->mongoConnection);
    }

    public function testCanGetCollection(): void
    {
        $collection = $this->mongoConnection->collection('test_collection');
        
        $this->assertInstanceOf(Collection::class, $collection);
    }

    public function testCanInsertAndFindDocument(): void
    {
        $collection = $this->mongoConnection->collection('test_collection');
        
        // Limpa a coleção antes do teste
        $collection->deleteMany([]);
        
        // Insere um documento de teste
        $testDocument = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => new \DateTime()
        ];
        
        $result = $collection->insertOne($testDocument);
        $this->assertNotNull($result->getInsertedId());
        
        // Busca o documento inserido
        $foundDocument = $collection->findOne(['_id' => $result->getInsertedId()]);
        
        $this->assertNotNull($foundDocument);
        $this->assertEquals('Test User', $foundDocument['name']);
        $this->assertEquals('test@example.com', $foundDocument['email']);
    }

    public function testCanUpdateDocument(): void
    {
        $collection = $this->mongoConnection->collection('test_collection');
        
        // Limpa a coleção antes do teste
        $collection->deleteMany([]);
        
        // Insere um documento
        $testDocument = ['name' => 'Original Name', 'email' => 'original@example.com'];
        $result = $collection->insertOne($testDocument);
        $insertedId = $result->getInsertedId();
        
        // Atualiza o documento
        $updateResult = $collection->updateOne(
            ['_id' => $insertedId],
            ['$set' => ['name' => 'Updated Name']]
        );
        
        $this->assertEquals(1, $updateResult->getModifiedCount());
        
        // Verifica se a atualização funcionou
        $updatedDocument = $collection->findOne(['_id' => $insertedId]);
        $this->assertEquals('Updated Name', $updatedDocument['name']);
    }

    public function testCanDeleteDocument(): void
    {
        $collection = $this->mongoConnection->collection('test_collection');
        
        // Limpa a coleção antes do teste
        $collection->deleteMany([]);
        
        // Insere um documento
        $testDocument = ['name' => 'To Delete', 'email' => 'delete@example.com'];
        $result = $collection->insertOne($testDocument);
        $insertedId = $result->getInsertedId();
        
        // Deleta o documento
        $deleteResult = $collection->deleteOne(['_id' => $insertedId]);
        
        $this->assertEquals(1, $deleteResult->getDeletedCount());
        
        // Verifica se o documento foi deletado
        $deletedDocument = $collection->findOne(['_id' => $insertedId]);
        $this->assertNull($deletedDocument);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção de teste após cada teste
        $collection = $this->mongoConnection->collection('test_collection');
        $collection->deleteMany([]);
    }
}
