<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate(): void
    {
        $user = User::factory()->create([
            'is_admin' => 1
        ]);

        $response = $this->postJson('api/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
            ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response =$this->postJson('api/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciales incorrectas'
            ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'is_admin' => 1
        ]);

        $loginResponse = $this->postJson('api/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        // Validar que el login fue exitoso
        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);

        // Obtener el token de autenticación
        $token = $loginResponse->json('token');

        // Realizar la solicitud de logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/logout');

        // Verificar que el logout fue exitoso
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        // Verificar que el token se eliminó de la BD
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
