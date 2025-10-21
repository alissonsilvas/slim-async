<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Interfaces\UseCases\CreateUserUseCaseInterface;
use App\Application\Interfaces\UseCases\DeleteUserUseCaseInterface;
use App\Application\Interfaces\UseCases\GetUserUseCaseInterface;
use App\Application\Interfaces\UseCases\ListUsersUseCaseInterface;
use App\Application\Interfaces\UseCases\UpdateUserUseCaseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends BaseController
{
    public function create(Request $request, Response $response, CreateUserUseCaseInterface $useCase, string $dtoClass): Response
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

    public function getById(Request $request, Response $response, string $id, GetUserUseCaseInterface $useCase): Response
    {
        try {
            $userResponse = $useCase->execute($id);

            return $this->successResponse($response, $userResponse->toArray());
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function update(Request $request, Response $response, string $id, UpdateUserUseCaseInterface $useCase, string $dtoClass): Response
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

    public function delete(Request $request, Response $response, string $id, DeleteUserUseCaseInterface $useCase): Response
    {
        try {
            $useCase->execute($id);

            return $this->noContentResponse($response);
        } catch (\Exception $e) {
            return $this->handleException($e, $response);
        }
    }

    public function list(Request $request, Response $response, ListUsersUseCaseInterface $useCase): Response
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
