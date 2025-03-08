<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *
     *     @OA\RequestBody(
     *              required=true,
     *
     *              @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="password", type="string", example="password"),
     *              ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="access_token", type="string", example="token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="user", type="object", example={"name":"John Doe","email":"test@example.com"}),
     *                 @OA\Property(property="status", type="string", example="Login successful"),
     *           )
     *     ),
     * )
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'status' => 'Login successful',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Logout successful"),
     *           )
     *     ),
     *
     *     @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Logout failed"),
     *           )
     *     ),
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json(['message' => 'Logout failed'], 500);
        }
    }
}
