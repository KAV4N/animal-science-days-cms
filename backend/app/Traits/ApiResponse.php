<?php
namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'payload' => $data,
        ], $status);
    }

    protected function paginatedResponse($paginator, $data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'payload' => $data ?? $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], $status);
    }

    protected function errorResponse($message, $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $status);
    }
}