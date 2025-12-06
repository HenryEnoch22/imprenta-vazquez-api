<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FindCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_all_customers(): void
    {
        Sanctum::actingAs(
            User::factory()->create(['is_admin' => 1]),
            ['*']
        );

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'business_name',
                        'representative_name',
                        'rfc',
                        'phone_number',
                        'created_at',
                        'updated_at',

                        'customer_address' => [
                            'id',
                            'postal_code',
                            'address',
                            'locality_name',
                            'federal_entity',
                            'neighborhood',
                            'municipality',
                            'between_streets',
                            'interior_number',
                            'exterior_number',
                            'created_at',
                            'updated_at',
                            'deleted_at',
                        ],

                        'user' => [
                            'id',
                            'username',
                            'email',
                            'email_verified_at',
                            'is_admin',
                            'created_at',
                            'updated_at',
                            'deleted_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_non_admin_users_cannot_get_all_customers(): void
    {
        Sanctum::actingAs(
            User::factory()->create(['is_admin' => 0]),
        );

        $response = $this->getJson('/api/customers');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);
    }

    public function test_admin_can_get_customer_by_id(): void
    {
        Sanctum::actingAs(
            $user = User::factory()->create(['is_admin' => 1]),
            ['*']
        );

        $customerAddress = \App\Models\CustomerAddress::factory()->create();

        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $user->id,
            'address_id' => $customerAddress->id,
        ]);

        $response = $this->getJson('/api/customers/' . $customer->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'business_name',
                    'representative_name',
                    'rfc',
                    'phone_number',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'is_admin',
                        'created_at',
                        'updated_at',
                    ],
                    'customer_address' => [
                        'id',
                        'postal_code',
                        'address',
                        'locality_name',
                        'federal_entity',
                        'neighborhood',
                        'municipality',
                        'between_streets',
                        'interior_number',
                        'exterior_number',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_non_admin_can_get_customer_by_id(): void
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

        $response = $this->getJson('/api/customers/' . $customer->id);
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);
    }
}
