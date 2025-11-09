<?php

namespace App\Services\Media;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    public function success($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function error(string $message, int $code = 500, $error = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => config('app.debug') ? $error : null
        ], $code);
    }

    public function notFound(string $resource = 'Resource'): JsonResponse
    {
        return $this->error("{$resource} not found", 404);
    }

    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

}
