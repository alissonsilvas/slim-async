<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Application\UseCases\User\GetUserUseCase;
use App\Application\UseCases\User\ListUsersUseCase;
use App\Application\UseCases\User\UpdateUserUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends BaseController
{
    public function create(Request $request, Response $response, CreateUserUseCase $useCase, string $dtoClass): Response
    {
        try {
            $data = $this->getJsonData($request);

            if ($data === null) {
                return $this->validationErrorResponse($response, 'Invalid JSON data');
            }

            $dto = $dtoClass::fromArray($data);
            $userResponse = $useCase->execute($dto);

            return $this->successResponse($response, $userResponse->toArray(), 201);
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function getById(Request $request, Response $response, string $id, GetUserUseCase $useCase): Response
    {
        try {
            $userResponse = $useCase->execute($id);

            return $this->successResponse($response, $userResponse->toArray());
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function update(Request $request, Response $response, string $id, UpdateUserUseCase $useCase, string $dtoClass): Response
    {
        try {
            $data = $this->getJsonData($request);

            if ($data === null) {
                return $this->validationErrorResponse($response, 'Invalid JSON data');
            }

            $dto = $dtoClass::fromArray($data);
            $userResponse = $useCase->execute($id, $dto);

            return $this->successResponse($response, $userResponse->toArray());
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function delete(Request $request, Response $response, string $id, DeleteUserUseCase $useCase): Response
    {
        try {
            $useCase->execute($id);

            return $this->noContentResponse($response);
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function list(Request $request, Response $response, ListUsersUseCase $useCase): Response
    {
        try {
            [$page, $limit] = $this->getPaginationParams($request);
            $users = $useCase->execute($page, $limit);

            return $this->successResponse($response, [
                'data' => $users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'count' => count($users),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }
}
