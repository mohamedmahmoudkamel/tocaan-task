<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Tymon\JWTAuth\Exceptions\{JWTException};
use Illuminate\Http\{JsonResponse, Response};

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => __('auth.login.invalid_credentials'),
                    'message' => __('auth.login.invalid_credentials_message'),
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = auth()->user();
            $tokenTTL = config('jwt.ttl');
            $token = JWTAuth::fromUser($user, ['ttl' => $tokenTTL]);

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $tokenTTL,
                'message' => __('auth.login.success'),
            ], Response::HTTP_OK);

        } catch (JWTException $e) {
            return response()->json([
                'error' => __('auth.login.token_creation_failed'),
                'message' => __('auth.login.token_creation_failed_message'),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            return response()->json([
                'error' => __('auth.login.failed'),
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
