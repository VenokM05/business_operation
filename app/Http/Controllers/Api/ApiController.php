<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Base controller for the /api/v1 endpoints. Centralises the response
 * envelope described in PRD Section 13 so every endpoint returns the same
 * shape: { success, data, message, meta? }.
 */
abstract class ApiController extends Controller
{
    /** Single record / payload. */
    protected function ok(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    /** Paginated collection with the standard meta window (PRD Section 13). */
    protected function paginated(LengthAwarePaginator $page, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $page->items(),
            'message' => $message,
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
                'next_page_url' => $page->nextPageUrl(),
                'prev_page_url' => $page->previousPageUrl(),
            ],
        ]);
    }

    /** Error envelope (data omitted on purpose per PRD). */
    protected function fail(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /** Resolve ?per_page with a hard cap of 100 (PRD Section 13). */
    protected function perPage(int $default = 15): int
    {
        return min(max((int) request()->query('per_page', (string) $default), 1), 100);
    }
}
