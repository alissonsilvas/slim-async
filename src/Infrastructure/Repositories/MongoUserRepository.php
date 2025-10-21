<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use MongoDB\Collection;

final class MongoUserRepository implements UserRepositoryInterface
{
    private Collection $collection;

    public function __construct(
        private MongoConnection $connection
    ) {
        $this->collection = $this->connection->collection('users');
    }

    public function save(User $user): void
    {
        $data = $user->toArray();
        $data['_id'] = $user->getId();

        $this->collection->replaceOne(
            ['_id' => $user->getId()],
            $data,
            ['upsert' => true]
        );
    }

    public function findById(string $id): ?User
    {
        $data = $this->collection->findOne(['_id' => $id]);

        if ($data === null) {
            return null;
        }

        return $this->hydrateUser($data);
    }

    public function findByEmail(string $email): ?User
    {
        $data = $this->collection->findOne(['email' => $email]);

        if ($data === null) {
            return null;
        }

        return $this->hydrateUser($data);
    }

    public function findByUsername(string $username): ?User
    {
        $data = $this->collection->findOne(['username' => $username]);

        if ($data === null) {
            return null;
        }

        return $this->hydrateUser($data);
    }

    public function findAll(int $page = 1, int $limit = 10): array
    {
        $skip = ($page - 1) * $limit;

        $cursor = $this->collection->find(
            [],
            [
                'skip' => $skip,
                'limit' => $limit,
                'sort' => ['created_at' => -1],
            ]
        );

        $users = [];
        foreach ($cursor as $data) {
            $users[] = $this->hydrateUser($data);
        }

        return $users;
    }

    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => $id]);

        return $result->getDeletedCount() > 0;
    }

    public function existsByEmail(string $email): bool
    {
        $count = $this->collection->countDocuments(['email' => $email]);

        return $count > 0;
    }

    public function existsByUsername(string $username): bool
    {
        $count = $this->collection->countDocuments(['username' => $username]);

        return $count > 0;
    }

    private function hydrateUser($data): User
    {
        $username = Username::fromString($data['username']);
        $email = Email::fromString($data['email']);
        $document = Document::create(
            DocumentType::from($data['type_doc']),
            $data['number_doc']
        );

        $user = User::create($username, $email, $document);

        // Usar reflexão para definir ID e datas
        $reflection = new \ReflectionClass($user);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, $data['_id']);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($user, new \DateTime($data['created_at']));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($user, new \DateTime($data['updated_at']));

        return $user;
    }
}
