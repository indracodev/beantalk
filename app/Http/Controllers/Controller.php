<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /** Envelope sukses standar: { success: true, data: ... } */
    protected function ok(mixed $data = null, int $status = 200, array $extra = []): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data] + $extra, $status);
    }

    /** Envelope gagal standar: { success: false, error: { code, message } } */
    protected function fail(string $code, string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'error' => compact('code', 'message') + $extra], $status);
    }
}
