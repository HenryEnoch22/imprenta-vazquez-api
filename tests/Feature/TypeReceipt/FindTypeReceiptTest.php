<?php

namespace Tests\Feature\TypeReceipt;

use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FindTypeReceiptTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_admin_can_get_all_type_receipts(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        // Crear algunos TypeReceipt de ejemplo
        TypeReceipt::factory()->count(3)->create();

        Sanctum::actingAs($adminUser, ['*']);

        // Hacer la solicitud GET a la ruta de type-receipts
        $response = $this->getJson('api/type-receipts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Asegurarse de que se devuelven 3 registros
    }

    public function test_admin_can_get_type_receipt_by_id(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        Sanctum::actingAs($adminUser, ['*']);

        // Hacer la solicitud GET a la ruta de type-receipts/{id}
        $response = $this->getJson("api/type-receipts/" . $typeReceipt->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $typeReceipt->id,
                    'name' => $typeReceipt->name,
                    'description' => $typeReceipt->description,
                    'receipt_category' => $typeReceipt->receipt_category,
                ],
            ]);
    }

    public function test_non_admin_cannot_get_type_receipt_by_id(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        Sanctum::actingAs($adminUser, ['*']);

        // Hacer la solicitud GET a la ruta de type-receipts/{id}
        $response = $this->getJson("api/type-receipts/" . $typeReceipt->id);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);
    }

    public function test_admin_cannot_get_nonexistent_type_receipt(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        $typeReceiptId = 500; // ID que no existe

        Sanctum::actingAs($adminUser, ['*']);

        // Hacer la solicitud GET a la ruta de type-receipts/{id}
        $response = $this->getJson("api/type-receipts/" . $typeReceiptId);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tipo de comprobante no encontrado.',
            ]);
    }
}
