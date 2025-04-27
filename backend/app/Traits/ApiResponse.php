<?php
namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
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