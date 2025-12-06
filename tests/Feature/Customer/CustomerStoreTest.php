<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_create_customer(): void
    {
        // 1) Usuario admin autenticado
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $payload = [
            // User
            'username'           => 'cliente_demo',
            'email'              => 'cliente@example.com',
            'password'           => 'password123',
            'password_confirmation' => 'password123',

            // CustomerAddress
            'postal_code'        => '01000',
            'address'            => 'Calle Falsa 123',
            'locality_name'      => 'Ciudad de México',
            'federal_entity'     => 'CDMX',
            'neighborhood'       => 'Centro',
            'municipality'       => 'Álvaro Obregón',
            'between_streets'    => 'Entre A y B',
            'interior_number'    => '2B',      // opcional
            'exterior_number'    => '10',

            // Customer
            'business_name'      => 'Imprenta Demo SA de CV',
            'representative_name'=> 'Juan Pérez', // opcional
            'rfc'                => 'XAXX010101000',
            'phone_number'       => '5512345678',
        ];

        // Hacer la petición
        $response = $this->postJson('/api/customers', $payload);

        // Verificar respuesta
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cliente creado exitosamente',
            ]);

        // Verificar que se creó el User
        $this->assertDatabaseHas('users', [
            'username' => 'cliente_demo',
            'email'    => 'cliente@example.com',
        ]);

        // Verificar que se creó la dirección
        $this->assertDatabaseHas('customer_addresses', [
            'postal_code' => '01000',
            'address'     => 'Calle Falsa 123',
        ]);

        // Verificar que se creó el customer
        $this->assertDatabaseHas('customers', [
            'business_name' => 'Imprenta Demo SA de CV',
            'rfc'           => 'XAXX010101000',
            'phone_number'  => '5512345678',
        ]);
    }

    public function test_non_admin_users_cannot_create_customers(): void
    {
        // 1) Usuario que no es admin autenticado
        $admin = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $payload = [
            // User
            'username'           => 'cliente_demo2',
            'email'              => 'cliente2@example.com',
            'password'           => 'password123',
            'password_confirmation' => 'password123',

            // CustomerAddress
            'postal_code'        => '01001',
            'address'            => 'Calle Falsa 1233',
            'locality_name'      => 'Ciudad de México',
            'federal_entity'     => 'CDMX',
            'neighborhood'       => 'Centro',
            'municipality'       => 'Álvaro Obregón 2',
            'between_streets'    => 'Entre A y B 2',
            'interior_number'    => '2B2',
            'exterior_number'    => '102',

            // Customer
            'business_name'      => 'Imprenta Demo 2 SA de CV',
            'representative_name'=> 'Juliam Peye',
            'rfc'                => 'XAXX010101001',
            'phone_number'       => '123456789',
        ];

        // Hacer la petición
        $response = $this->postJson('/api/customers', $payload);

        // Verificar respuesta
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);

        // Verificar que se creó el User
        $this->assertDatabaseMissing('users', [
            'username' => 'cliente_demo2',
            'email'    => 'cliente2@example.com',
        ]);

        // Verificar que se creó la dirección
        $this->assertDatabaseMissing('customer_addresses', [
            'postal_code' => '01001',
            'address'     => 'Calle Falsa 1233',
        ]);

        // Verificar que se creó el customer
        $this->assertDatabaseMissing('customers', [
            'business_name' => 'Imprenta Demo 2 SA de CV',
            'rfc'           => 'XAXX010101001',
            'phone_number'  => '123456789',
        ]);
    }

    public function test_create_customers_with_valid_errors(): void
    {
        // 1) Usuario admin autenticado (para pasar el auth:sanctum de la ruta)
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($admin, ['*']);

        // 2) Payload con varios errores de validación
        $payload = [
            // USER
            'username' => '',                         // required → error
            'email' => 'correo-no-valido',            // email → error
            'password' => '123',                      // min:8 + confirmed → error
            //'password_confirmation' => '12345678', // Se omitime para que falle 'confirmed'

            // CUSTOMER
            'business_name' => '',                    // required → error
            'representative_name' => null,            // nullable → no error
            'rfc' => 'RFCDEMOPRUEBA',                 // size:13 OK
            'phone_number' => '',                     // required → error

            // ADDRESS
            'postal_code' => '123',                   // size:5 → error
            'address' => '',                          // required → error
            'locality_name' => '',                    // required → error
            'federal_entity' => '',                   // required → error
            'neighborhood' => '',                     // required → error
            'municipality' => '',                     // required → error
            'between_streets' => '',                  // required → error
            'interior_number' => null,                // nullable → ok
            'exterior_number' => '',                  // required → error
        ];

        // Petición
        $response = $this->postJson('/api/customers', $payload);

        // Debe responder 422 Unprocessable Entity
        $response->assertStatus(422);

        // Comprobar que hay errores de validación en campos clave
        $response->assertJsonValidationErrors([
            'username',
            'email',
            'password',
            'business_name',
            'phone_number',
            'postal_code',
            'address',
            'locality_name',
            'federal_entity',
            'neighborhood',
            'municipality',
            'between_streets',
            'exterior_number',
        ]);

        // Verificar que use mensajes personalizados
        $response->assertJsonFragment([
            'El nombre de usuario es obligatorio.',
        ]);

        $response->assertJsonFragment([
            'El correo electrónico no tiene un formato válido.',
        ]);

        $response->assertJsonFragment([
            'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $response->assertJsonFragment([
            'Las contraseñas no coinciden.',
        ]);

        $response->assertJsonFragment([
            'El nombre comercial es obligatorio.',
        ]);

        $response->assertJsonFragment([
            'El código postal debe tener 5 caracteres.',
        ]);
    }
}
