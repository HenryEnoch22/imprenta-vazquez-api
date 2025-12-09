<?php

namespace PrintRequest;

use App\Models\Customer;
use App\Models\PrintJobRequest;
use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdatePrintJobRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cliente puede actualizar su propia solicitud en estado editable.
     */
    public function test_customer_can_update_own_print_job_request(): void
    {
        Storage::fake('print-files');

        // Usuario cliente (no admin)
        $user = User::factory()->create(['is_admin' => 0]);

        // Customer asociado a ese user
        $customer = Customer::factory()->create([
            'user_id' => $user->id,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        // Archivo original
        Storage::disk('print-files')->put('original.pdf', 'contenido');

        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Nombre original',
            'description'     => 'Descripción original',
            'file_path'       => 'original.pdf',
            'status'          => PrintJobRequest::STATUS_PENDING, // editable
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'name'            => 'Nombre actualizado',
            'description'     => 'Descripción actualizada',
            'quantity'        => 50,
            'paper_size'      => 2,
            'paper_type'      => 3,
            'tint_colors'     => [10, 20, 30, 40],
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud actualizada exitosamente.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('print_job_requests', [
            'id'          => $printJob->id,
            'name'        => 'Nombre actualizado',
            'description' => 'Descripción actualizada',
            'quantity'    => 50,
            'status'      => PrintJobRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Cliente puede actualizar su solicitud y reemplazar el archivo PDF.
     */
    public function test_customer_can_update_print_job_and_replace_file(): void
    {
        Storage::fake('print-files');

        $user = User::factory()->create(['is_admin' => 0]);

        $customer = Customer::factory()->create([
            'user_id' => $user->id,
        ]);

        $typeReceipt = TypeReceipt::factory()->create();

        // Archivo original almacenado
        Storage::disk('print-files')->put('original.pdf', 'contenido');
        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Trabajo original',
            'file_path'       => 'original.pdf',
            'status'          => PrintJobRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($user, ['*']);

        $newFile = UploadedFile::fake()->create('nuevo.pdf', 300, 'application/pdf');

        $payload = [
            'name'      => 'Trabajo con archivo nuevo',
            'file_path' => $newFile,
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud actualizada exitosamente.',
            ]);

        $printJob->refresh();

        // El path cambió
        $this->assertNotEquals('original.pdf', $printJob->file_path);

        // El archivo nuevo existe y el viejo fue borrado
        Storage::disk('print-files')->assertExists($printJob->file_path);
        Storage::disk('print-files')->assertMissing('original.pdf');
    }

    /**
     * Admin NO puede modificar el contenido de la solicitud.
     */
    public function test_admin_cannot_update_print_job_request(): void
    {
        Storage::fake('print-files');

        $admin = User::factory()->create(['is_admin' => 1]);

        $customer = Customer::factory()->create();
        $typeReceipt = TypeReceipt::factory()->create();

        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Original',
            'status'          => PrintJobRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $payload = [
            'name' => 'Intento de cambio por admin',
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Un administrador no puede modificar el contenido de la solicitud.',
            ]);

        // El registro permanece igual
        $this->assertDatabaseHas('print_job_requests', [
            'id'   => $printJob->id,
            'name' => 'Original',
        ]);
    }

    /**
     * Cliente no puede actualizar la solicitud de otro cliente.
     */
    public function test_customer_cannot_update_other_customer_print_job_request(): void
    {
        Storage::fake('print-files');

        // Usuario A
        $userA = User::factory()->create(['is_admin' => 0]);
        $customerA = Customer::factory()->create(['user_id' => $userA->id]);

        // Usuario B (dueño real de la solicitud)
        $userB = User::factory()->create(['is_admin' => 0]);
        $customerB = Customer::factory()->create(['user_id' => $userB->id]);

        $typeReceipt = TypeReceipt::factory()->create();

        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customerB->id, // pertenece a B
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Trabajo de B',
            'status'          => PrintJobRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($userA, ['*']);

        $payload = [
            'name' => 'Intento de A',
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No autorizado para modificar esta solicitud de impresión.',
            ]);

        $this->assertDatabaseHas('print_job_requests', [
            'id'   => $printJob->id,
            'name' => 'Trabajo de B',
        ]);
    }

    /**
     * Cliente no puede editar una solicitud en estado no editable.
     * (por ejemplo, terminada).
     */
    public function test_customer_cannot_update_print_job_in_non_editable_status(): void
    {
        Storage::fake('print-files');

        $user = User::factory()->create(['is_admin' => 0]);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $typeReceipt = TypeReceipt::factory()->create();

        // Suponemos que STATUS_FINISHED no es editable por canBeEditedByClient()
        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Trabajo terminado',
            'status'          => PrintJobRequest::STATUS_COMPLETED,
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'name' => 'Intento de modificación',
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'No se puede editar una solicitud en este estado.',
            ]);

        $this->assertDatabaseHas('print_job_requests', [
            'id'   => $printJob->id,
            'name' => 'Trabajo terminado',
        ]);
    }

    /**
     * Si la solicitud estaba REJECTED o DECLINED,
     * al editarla vuelve a PENDING y limpia las razones.
     */
    public function test_update_rejected_or_declined_print_job_resets_status_and_reasons(): void
    {
        Storage::fake('print-files');

        $user = User::factory()->create(['is_admin' => 0]);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $typeReceipt = TypeReceipt::factory()->create();

        $printJob = PrintJobRequest::factory()->create([
            'customer_id'       => $customer->id,
            'type_receipt_id'   => $typeReceipt->id,
            'name'              => 'Trabajo rechazado',
            'status'            => PrintJobRequest::STATUS_REJECTED,
            'reason_rejection'  => 'Archivo ilegible',
        ]);

        Sanctum::actingAs($user, ['*']);

        $payload = [
            'name' => 'Trabajo corregido',
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Solicitud actualizada exitosamente.',
            ]);

        $printJob->refresh();

        $this->assertEquals(PrintJobRequest::STATUS_PENDING, $printJob->status);
        $this->assertNull($printJob->reason_rejection);
        $this->assertNull($printJob->reason_declined);
        $this->assertEquals('Trabajo corregido', $printJob->name);
    }

    /**
     * Usuario invitado no puede actualizar solicitudes.
     */
    public function test_guest_cannot_update_print_job_request(): void
    {
        Storage::fake('print-files');

        $customer = Customer::factory()->create();
        $typeReceipt = TypeReceipt::factory()->create();

        $printJob = PrintJobRequest::factory()->create([
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Trabajo cualquiera',
            'status'          => PrintJobRequest::STATUS_PENDING,
        ]);

        $payload = [
            'name' => 'Intento sin auth',
        ];

        $response = $this->putJson(
            route('print-jobs.update', $printJob->id),
            $payload
        );

        $response->assertStatus(401);

        $this->assertDatabaseHas('print_job_requests', [
            'id'   => $printJob->id,
            'name' => 'Trabajo cualquiera',
        ]);
    }

}
