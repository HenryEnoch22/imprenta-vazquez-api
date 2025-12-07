<?php

namespace Tests\Feature\PrintRequest;

use App\Models\PrintJobRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeletePrintJobRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_print_job_request(): void
    {
        // Usuario autenticado (puede ser admin o no, tu método no valida roles)
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        // Creamos una solicitud de impresión
        $printJob = PrintJobRequest::factory()->create();
        \Log::info([
            'printJob before delete' => $printJob,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->deleteJson('/api/print-jobs/' . $printJob->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud de impresión eliminada exitosamente.',
            ]);

        // verificamos que esté soft-deleted
        $this->assertSoftDeleted('print_job_requests', [
            'id' => $printJob->id,
        ]);
    }

    public function test_authenticated_user_gets_500_when_deleting_nonexistent_print_job_request(): void
    {
        $user = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $nonExistingId = 9999;

        $response = $this->deleteJson('/api/print-jobs/' . $nonExistingId);

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_delete_print_job_request(): void
    {
        $printJob = PrintJobRequest::factory()->create();

        $response = $this->deleteJson('/api/print-jobs/' . $printJob->id);

        // Si la ruta está protegida con auth:sanctum debería devolver 401
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
