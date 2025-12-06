<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteCustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_admin_can_delete_customer(): void
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
        $response = $this->deleteJson('/api/customers/' . $customer->id);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cliente eliminado exitosamente',
            ]);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);

        $this->assertSoftDeleted('customer_addresses', [
            'id' => $customerAddress->id,
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_non_admin_cannot_delete_customer(): void
    {
        Sanctum::actingAs(
            $user = User::factory()->create(['is_admin' => 0]),
        );

        $customerAddress = \App\Models\CustomerAddress::factory()->create();

        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $user->id,
            'address_id' => $customerAddress->id,
        ]);
        $response = $this->deleteJson('/api/customers/' . $customer->id);
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);

    }
}
