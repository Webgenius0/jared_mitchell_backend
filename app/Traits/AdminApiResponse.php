<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

trait AdminApiResponse
{
    /**
     * 200 / 201 success response.
     */
    protected function success(string $message, array $data = [], ?string $redirect = null, int $status = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($redirect !== null) {
            $payload['redirect'] = $redirect;
        }

        if (! empty($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * Validation / business-logic error response.
     */
    protected function error(string $message, array $errors = [], int $status = 422): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Convenience: wrap Laravel Validator errors into our format.
     */
    protected function validationError(
        Validator $validator,
        string $message = 'Please fix the errors below.',
        int $status = 422
    ): JsonResponse {
        return $this->error($message, $validator->errors()->toArray(), $status);
    }
}
