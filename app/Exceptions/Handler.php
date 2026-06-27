<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    public function render($request, Throwable $e)
    {
        // 🔥 TAMPILKAN ERROR DETAIL
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint tidak ditemukan'
            ], 404);
        }

        // 🔥 UNTUK ERROR LAIN, TAMPILKAN DETAIL (HANYA DI DEVELOPMENT)
        if (env('APP_DEBUG', false)) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }

        return parent::render($request, $e);
    }
}