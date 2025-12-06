<?php

namespace Tests\Feature\TypeReceipt;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TypeReceiptStoreTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_admin_can_create_type_receipt(): void
    {
        $adminUser = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $payload = [
            'type_receipt_category_id' => 1,
            'name' => 'Factura',
            'description' => 'Comprobante fiscal digital',
        ];

        $response = $this->actingAs($adminUser)->postJson('/api/type-receipts', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'type_receipt_category_id',
                         'name',
                         'description',
                         'created_at',
                         'updated_at',
                     ],
                 ]);

        $this->assertDatabaseHas('type_receipts', [
            'name' => 'Factura',
            'type_receipt_category_id' => 1,
        ]);
    }
}
