<?php

namespace Tests\Feature\TypeReceipt;

use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateTypeReciptTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_type_receipt(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        $payload = [
            'receipt_category' => 'Varios',
            'name'             => 'Nota de remisión',
            'description'      => 'Descripción actualizada',
        ];

        Sanctum::actingAs($adminUser, ['*']);

        $response = $this->putJson('api/type-receipts/' . $typeReceipt->id, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tipo de comprobante actualizado exitosamente.',
            ]);

        // Verificar valores actualizados en BD
        $this->assertDatabaseHas('type_receipts', [
            'id'               => $typeReceipt->id,
            'receipt_category' => 'Varios',
            'name'             => 'Nota de remisión',
            'description'      => 'Descripción actualizada',
        ]);
    }

    public function test_non_admin_cannot_update_type_receipt(): void
    {
        $normalUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        $typeReceipt = TypeReceipt::factory()->create([
            'receipt_category' => 'Impresión',
            'name' => 'Nota de venta',
            'description' => 'Descripción original',
        ]);

        $payload = [
            'receipt_category' => 'Varios',
            'name'             => 'Nota de remisión',
            'description'      => 'Descripción actualizada',
        ];

        Sanctum::actingAs($normalUser, ['*']);

        $response = $this->putJson('api/type-receipts/' . $typeReceipt->id, $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);

        // Verificar que NO se haya cambiado en la BD
        $this->assertDatabaseHas('type_receipts', [
            'id'               => $typeReceipt->id,
            'receipt_category' => 'Impresión',
            'name'             => 'Nota de venta',
            'description'      => 'Descripción original',
        ]);
    }

    public function test_admin_cannot_update_type_receipt_with_invalid_category(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        $typeReceipt = TypeReceipt::factory()->create([
            'receipt_category' => 'Impresión',
            'name' => 'Nota de venta',
            'description' => 'Descripción original',
        ]);

        // Categoría inválida (no está en ['Impresión', 'Varios'])
        $payload = [
            'receipt_category' => 'Invalida',
            'name'             => 'Nota de remisión',
            'description'      => 'Descripción actualizada',
        ];

        Sanctum::actingAs($adminUser, ['*']);

        $response = $this->putJson('api/type-receipts/' . $typeReceipt->id, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receipt_category']);

        // Validar el mensaje de error específico
        $response->assertJsonFragment([
            'receipt_category' => [
                'El campo categoría debe ser uno de los siguientes valores: Impresión, Varios.',
            ],
        ]);

        // No debe haberse modificado el registro
        $this->assertDatabaseHas('type_receipts', [
            'id'               => $typeReceipt->id,
            'receipt_category' => 'Impresión',
            'name'             => 'Nota de venta',
            'description'      => 'Descripción original',
        ]);
    }

    public function test_admin_gets_404_when_updating_nonexistent_type_receipt(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($adminUser, ['*']);

        $payload = [
            'receipt_category' => 'Impresión',
            'name'             => 'Cualquier nombre',
            'description'      => 'Cualquier descripción',
        ];

        $response = $this->putJson('api/type-receipts/9999', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tipo de comprobante no encontrado.',
            ]);
    }
}
