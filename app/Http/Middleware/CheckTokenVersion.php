<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckTokenVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse the token from the request
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            // Get the current user based on the JWT token
            $user = JWTAuth::user();

            // Check if the token version matches the user's stored version
            if ($payload->get('token_version') !== $user->token_version) {
                // Log the token mismatch for debugging purposes
                Log::warning('Token invalidated due to version mismatch.', [
                    'user_id' => $user->id,
                    'token_version' => $payload->get('token_version'),
                    'stored_version' => $user->token_version,
                ]);

                return response()->json(['error' => 'Token has been invalidated'], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Token validation failed: ' . $e->getMessage());

            // Handle the case where token parsing or validation fails
            return response()->json(['error' => 'Invalid token or token expired'], 401);
        }
    }
}
