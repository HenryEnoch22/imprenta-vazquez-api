<?php

namespace Tests\Feature\TypeReceipt;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TypeReceiptStoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_admin_can_create_type_receipt(): void
    {
        $adminUser = \App\Models\User::factory()->create([
            'is_admin' => 1,
        ]);

        $payload = [
            'receipt_category' => 'Impresión',
            'name' => 'Nota de venta',
            'description' => 'Notitas de venta',
        ];

        Sanctum::actingAs($adminUser, ['*']);
        $response = $this->postJson('api/type-receipts', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Tipo de comprobante creado exitosamente.',
            ]);

        $this->assertDatabaseHas('type_receipts', [
            'name' => 'Nota de venta',
            'receipt_category' => 'Impresión',
        ]);
    }

    public function test_non_admin_cannot_create_type_receipt(): void
    {
        $normalUser = \App\Models\User::factory()->create([
            'is_admin' => 0,
        ]);

        $payload = [
            'receipt_category' => 'Impresión',     // válido
            'name' => 'Nota de venta',
            'description' => 'Notitas de venta',
        ];

        Sanctum::actingAs($normalUser, ['*']);

        $response = $this->postJson('api/type-receipts', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);

        // Asegurarnos de que NO se creó ningún registro
        $this->assertDatabaseCount('type_receipts', 0);
    }

    public function test_admin_cannot_create_type_receipt_with_invalid_category(): void
    {
        $adminUser = \App\Models\User::factory()->create([
            'is_admin' => 1,
        ]);

        // receipt_category inválido (no está en ['Impresión', 'Varios'])
        $payload = [
            'receipt_category' => 'Invalida',
            'name' => 'Nota de venta',
            'description' => 'Notitas de venta',
        ];

        Sanctum::actingAs($adminUser, ['*']);

        $response = $this->postJson('api/type-receipts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receipt_category']);

        // Verificar el mensaje de error específico
        $response->assertJsonFragment([
            'receipt_category' => [
                'El campo categoría debe ser uno de los siguientes valores: Impresión, Varios.',
            ],
        ]);

        // No debe haberse insertado nada
        $this->assertDatabaseCount('type_receipts', 0);
    }
}
