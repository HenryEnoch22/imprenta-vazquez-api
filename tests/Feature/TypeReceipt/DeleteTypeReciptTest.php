<?php

namespace Tests\Feature\TypeReceipt;

use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteTypeReciptTest extends TestCase
{
   use RefreshDatabase;

    public function test_admin_can_delete_type_receipt(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        Sanctum::actingAs($adminUser, ['*']);

        $response = $this->deleteJson('api/type-receipts/' . $typeReceipt->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tipo de comprobante eliminado exitosamente.',
            ]);

        // Como el modelo usa SoftDeletes, verificamos soft delete
        $this->assertSoftDeleted('type_receipts', [
            'id' => $typeReceipt->id,
        ]);
    }

    public function test_non_admin_cannot_delete_type_receipt(): void
    {
        $normalUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        Sanctum::actingAs($normalUser, ['*']);

        $response = $this->deleteJson('api/type-receipts/' . $typeReceipt->id);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario no autorizado',
            ]);

        // Aseguramos que NO se borró
        $this->assertDatabaseHas('type_receipts', [
            'id'          => $typeReceipt->id,
            'deleted_at'  => null,
        ]);
    }

    public function test_admin_gets_404_when_deleting_nonexistent_type_receipt(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($adminUser, ['*']);
        $nonExistingId = 9999;
        $response = $this->deleteJson('api/type-receipts/' . $nonExistingId);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tipo de comprobante no encontrado.',
            ]);
    }
}
