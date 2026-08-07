<?php

namespace App\Http\Middleware;

use App\Modules\Students\Exceptions\StudentLinkageException;
use App\Modules\Students\Services\StudentAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user resolves to a Student before any student API
 * controller runs. Returns a meaningful JSON 404/403 instead of a bare error.
 */
class EnsureStudentLinked
{
    public function __construct(private readonly StudentAuthService $studentAuth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->studentAuth->resolveForRequest($user);
        } catch (StudentLinkageException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return $next($request);
    }
}
