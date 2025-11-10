<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request) : JsonResponse
    {
        $data = $request->validated();

        if (!Auth::attempt(
            ['username' => $data['username'], 'password' => $data['password']] )) {

            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = Auth::user();

        // Eliminar tokens existentes (opcional)
        $user->tokens()->delete();

        // Genera nombre del token
        $nameToken = $data['username'] . '-token' ?? 'api-token';

        // Establecer fecha de expiración del token (14 días)
        $expiresAt = now()->addDays(14);

        // Crear nuevo token
        $token = $user->createToken($nameToken, ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Revocar el token actual
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
