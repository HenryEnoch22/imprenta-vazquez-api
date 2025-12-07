<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_customer(): void
    {
        // Autenticar como admin
        Sanctum::actingAs(
            $user = User::factory()->create(['is_admin' => 1]),
            ['*']
        );
        // Crear dirección del cliente
        $customerAddress = \App\Models\CustomerAddress::factory()->create();

        // Crear cliente asociado al usuario y a la dirección
        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $user->id,
            'address_id' => $customerAddress->id,
        ]);

        //  Payload con datos nuevos y válidos según la clase UpdateCustomerRequest
        $payload = [
            // Customer
            'business_name' => 'Empresa Actualizada SA de CV',
            'representative_name' => 'Nuevo Representante',
            'rfc' => 'ABCD900101XYZ',
            'phone_number'  => '+525512345678',

            // Address
            'postal_code'     => '12345',
            'address'         => 'Calle Actualizada 123',
            'locality_name'   => 'Localidad Nueva',
            'federal_entity'  => 'Entidad Nueva',
            'neighborhood'    => 'Colonia Nueva',
            'municipality'    => 'Municipio Nuevo',
            'between_streets' => 'Entre Calle A y Calle B',
            'interior_number' => '2B',
            'exterior_number' => '100',
        ];

        //Hacer la petición PUT/PATCH al endpoint
        $response = $this->putJson('/api/customers/' . $customer->id, $payload);

        //Verificar respuesta
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cliente actualizado exitosamente',
            ]);

        //Verificar que el customer se actualizó en la BD
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'business_name' => 'Empresa Actualizada SA de CV',
            'representative_name' => 'Nuevo Representante',
            'rfc' => 'ABCD900101XYZ',
            'phone_number' => '+525512345678',
        ]);

        //Verificar que la dirección se actualizó en la BD
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $customerAddress->id,
            'postal_code' => '12345',
            'address' => 'Calle Actualizada 123',
            'locality_name' => 'Localidad Nueva',
            'federal_entity'=> 'Entidad Nueva',
            'neighborhood' => 'Colonia Nueva',
            'municipality' => 'Municipio Nuevo',
            'between_streets' => 'Entre Calle A y Calle B',
            'interior_number' => '2B',
            'exterior_number' => '100',
        ]);
    }

    public function test_non_admin_cannot_update_customer(): void
    {
        // Autenticar como admin
        Sanctum::actingAs(
            $user = User::factory()->create(['is_admin' => 0]),
        );
        // Crear dirección del cliente
        $customerAddress = \App\Models\CustomerAddress::factory()->create();

        // Crear cliente asociado al usuario y a la dirección
        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $user->id,
            'address_id' => $customerAddress->id,
        ]);

        //  Payload con datos nuevos y válidos según la clase UpdateCustomerRequest
        $payload = [
            // Customer
            'business_name' => 'Empresa Actualizada SA de CV',
            'representative_name' => 'Nuevo Representante',
            'rfc' => 'ABCD900101XYZ',
            'phone_number'  => '+525512345678',

            // Address
            'postal_code'     => '12345',
            'address'         => 'Calle Actualizada 123',
            'locality_name'   => 'Localidad Nueva',
            'federal_entity'  => 'Entidad Nueva',
            'neighborhood'    => 'Colonia Nueva',
            'municipality'    => 'Municipio Nuevo',
            'between_streets' => 'Entre Calle A y Calle B',
            'interior_number' => '2B',
            'exterior_number' => '100',
        ];

        //Hacer la petición PUT/PATCH al endpoint
        $response = $this->putJson('/api/customers/' . $customer->id, $payload);

        //Verificar respuesta
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);
    }

    public function test_admin_cannot_update_customer_with_invalid_data(): void
    {
        Sanctum::actingAs(
            $user = User::factory()->create(['is_admin' => 1]),
            ['*']
        );

        //Crear dirección del cliente
        $customerAddress = \App\Models\CustomerAddress::factory()->create();

        // Crear cliente asociado al usuario y a la dirección
        $customer = \App\Models\Customer::factory()->create([
            'user_id'    => $user->id,
            'address_id' => $customerAddress->id,
        ]);

        // Payload con errores de validación
        $payload = [
            // Customer – errores:
            'business_name' => '',
            'representative_name' => str_repeat('A', 200),
            'rfc' => 'ABC123',
            'phone_number' => 'TEL-XXX',

            // Address – errores:
            'postal_code' => '12A',
            'address' => '',
            'locality_name' => '',
            'federal_entity' => '',
            'neighborhood' => '',
            'municipality' => '',
            'between_streets' => '',
            'interior_number' => str_repeat('1', 20),
            'exterior_number' => '',
        ];

        // Hacer la petición PUT al endpoint
        $response = $this->putJson('/api/customers/' . $customer->id, $payload);

        // 422 Unprocessable Entity
        $response->assertStatus(422);

        // 7) Verificar que hay errores en los campos clave
        $response->assertJsonValidationErrors([
            'business_name',
            'representative_name',
            'rfc',
            'phone_number',
            'postal_code',
            'address',
            'locality_name',
            'federal_entity',
            'neighborhood',
            'municipality',
            'between_streets',
            'interior_number',
            'exterior_number',
        ]);

        // Ignora lo que hay en memoria y solo toma lo que hay en la BD
        $customer->refresh();
        $customerAddress->refresh();

        $this->assertNotEquals('', $customer->business_name);
        $this->assertNotEquals('ABC123', $customer->rfc);
        $this->assertNotEquals('TEL-XXX', $customer->phone_number);

        $this->assertNotEquals('12A', $customerAddress->postal_code);
        $this->assertNotEquals('', $customerAddress->address);
    }
}
