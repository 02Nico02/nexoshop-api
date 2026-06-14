<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponder
{
    public function list(array $data, array $meta = [], int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    public function detail(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'data' => $data,
        ], $status);
    }

    public function error(string $code, string $message, int $status = 400, array $details = []): JsonResponse
    {
        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== []) {
            $payload['error']['details'] = $details;
        }

        return new JsonResponse($payload, $status);
    }

    /**
     * @param array<int, array{field:string,message:string}> $errors
     */
    public function validationErrors(array $errors, int $status = 422): JsonResponse
    {
        return new JsonResponse([
            'errors' => $errors,
        ], $status);
    }
}
