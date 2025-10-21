<?php

declare(strict_types=1);

namespace Tests\Integration\Endpoints;

use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use App\Presentation\Container;
use App\Presentation\Controllers\UserController;
use App\Presentation\RouteFactory;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class HttpUserEndpointsTest extends TestCase
{
    private App $app;
    private MongoConnection $connection;
    private MongoUserRepository $repository;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        
        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
        
        // Configura o app Slim
        $this->app = AppFactory::create();
        
        // Registra as rotas
        RouteFactory::createCrudRoutes(
            $this->app,
            '/users',
            UserController::class,
            \App\Application\Interfaces\UseCases\CreateUserUseCaseInterface::class,
            \App\Application\Interfaces\UseCases\GetUserUseCaseInterface::class,
            \App\Application\Interfaces\UseCases\UpdateUserUseCaseInterface::class,
            \App\Application\Interfaces\UseCases\DeleteUserUseCaseInterface::class,
            \App\Application\Interfaces\UseCases\ListUsersUseCaseInterface::class,
            \App\Application\DTOs\CreateUserDTO::class,
            \App\Application\DTOs\UpdateUserDTO::class
        );
    }

    public function testShouldCreateUserViaHttpPost(): void
    {
        $request = $this->createRequest('POST', '/users', [
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'type_doc' => 'CPF',
            'number_doc' => '11144477735'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('id', $body);
        $this->assertEquals('johndoe', $body['username']);
        $this->assertEquals('john@example.com', $body['email']);
        $this->assertEquals('CPF', $body['type_doc']);
        $this->assertEquals('11144477735', $body['number_doc']);
    }

    public function testShouldReturnValidationErrorForInvalidData(): void
    {
        $request = $this->createRequest('POST', '/users', [
            'username' => 'jo', // Muito curto
            'email' => 'invalid-email',
            'type_doc' => 'CPF',
            'number_doc' => '11144477735'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testShouldGetUserByIdViaHttpGet(): void
    {
        // Primeiro cria um usuário
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        $this->repository->save($user);
        
        // Busca o usuário para obter o ID
        $foundUser = $this->repository->findByEmail('john@example.com');
        $this->assertNotNull($foundUser);
        
        $request = $this->createRequest('GET', "/users/{$foundUser->getId()}");
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertEquals($foundUser->getId(), $body['id']);
        $this->assertEquals('johndoe', $body['username']);
        $this->assertEquals('john@example.com', $body['email']);
    }

    public function testShouldReturnNotFoundForNonExistentUser(): void
    {
        $request = $this->createRequest('GET', '/users/non-existent-id');
        $response = $this->app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testShouldUpdateUserViaHttpPut(): void
    {
        // Primeiro cria um usuário
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        $this->repository->save($user);
        
        // Busca o usuário para obter o ID
        $foundUser = $this->repository->findByEmail('john@example.com');
        $this->assertNotNull($foundUser);
        
        $request = $this->createRequest('PUT', "/users/{$foundUser->getId()}", [
            'username' => 'janedoe',
            'email' => 'jane@example.com'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertEquals($foundUser->getId(), $body['id']);
        $this->assertEquals('janedoe', $body['username']);
        $this->assertEquals('jane@example.com', $body['email']);
    }

    public function testShouldDeleteUserViaHttpDelete(): void
    {
        // Primeiro cria um usuário
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        $this->repository->save($user);
        
        // Busca o usuário para obter o ID
        $foundUser = $this->repository->findByEmail('john@example.com');
        $this->assertNotNull($foundUser);
        
        $request = $this->createRequest('DELETE', "/users/{$foundUser->getId()}");
        $response = $this->app->handle($request);

        $this->assertEquals(204, $response->getStatusCode());
        
        // Verifica se o usuário foi realmente deletado
        $request = $this->createRequest('GET', "/users/{$foundUser->getId()}");
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testShouldListUsersViaHttpGet(): void
    {
        // Cria alguns usuários
        $user1 = User::create(
            Username::fromString('user1'),
            Email::fromString('user1@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        
        $user2 = User::create(
            Username::fromString('user2'),
            Email::fromString('user2@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);
        
        $request = $this->createRequest('GET', '/users?page=1&limit=10');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('pagination', $body);
        $this->assertCount(2, $body['data']);
        $this->assertEquals(1, $body['pagination']['page']);
        $this->assertEquals(10, $body['pagination']['limit']);
    }

    public function testShouldHandleInvalidJsonInRequest(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', '/users');
        $request->getBody()->write('invalid json');
        
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Invalid JSON data', $body['error']);
    }

    public function testShouldValidatePaginationParameters(): void
    {
        $request = $this->createRequest('GET', '/users?page=0&limit=0'); // Parâmetros inválidos
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertArrayHasKey('pagination', $body);
        $this->assertEquals(1, $body['pagination']['page']); // Deve ser corrigido para 1
        $this->assertEquals(1, $body['pagination']['limit']); // Deve ser corrigido para 1
    }

    public function testShouldCreateUserWithCNPJ(): void
    {
        $request = $this->createRequest('POST', '/users', [
            'username' => 'company',
            'email' => 'company@example.com',
            'type_doc' => 'CNPJ',
            'number_doc' => '11222333000181'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        
        $body = $response->getBody();
        $body->rewind();
        $bodyContents = $body->getContents();
        $body = json_decode($bodyContents, true);
        $this->assertEquals('company', $body['username']);
        $this->assertEquals('company@example.com', $body['email']);
        $this->assertEquals('CNPJ', $body['type_doc']);
        $this->assertEquals('11222333000181', $body['number_doc']);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    private function createRequest(string $method, string $uri, ?array $data = null): \Psr\Http\Message\ServerRequestInterface
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri);
        
        if ($data !== null) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $request->getBody()->write(json_encode($data));
        }
        
        return $request;
    }
}
