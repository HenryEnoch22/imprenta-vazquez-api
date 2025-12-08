<?php

namespace Tests\Feature\PrintRequest;

use App\Models\Customer;
use App\Models\PrintJobRequest;
use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StorePrintJobRequestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_customer_can_create_print_job_request(): void
    {
        $customerUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($customerUser, ['*']);

        Storage::fake('print-files');

        $customer = Customer::factory()->create();
        $typeReceipt = TypeReceipt::factory()->create(); // id distinto de 1 normalmente

        $file = UploadedFile::fake()->create('trabajo.pdf', 500, 'application/pdf');

        $payload = [
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Tarjetas de presentación',
            'file_path'       => $file,
            'description'     => 'Trabajo de impresión de prueba',

            // Condicionales: como type_receipt_id != 1, no son obligatorios
            'folio'           => '590',
            'copies_number'   => '2',
            'copies_colors'   => [1,2],

            // Campos obligatorios
            'tint_colors'     => [100, 50, 25, 10],
            'paper_size'      => 1,
            'paper_type'      => 2,
            'quantity'        => 100,
        ];

        $response = $this->postJson('api/print-jobs', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Solicitud de impresión creada exitosamente.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_id',
                    'type_receipt_id',
                    'name',
                    'file_path',
                    'status',
                ],
            ]);

        $printJob = PrintJobRequest::first();
        $this->assertNotNull($printJob);
        $this->assertEquals($customer->id, $printJob->customer_id);
        $this->assertEquals($typeReceipt->id, $printJob->type_receipt_id);
        $this->assertEquals(PrintJobRequest::STATUS_PENDING, $printJob->status);

        Storage::disk('print-files')->assertExists($printJob->file_path);
    }

    public function test_admin_cannot_create_print_job_request(): void
    {
        $adminUser = User::factory()->create([
            'is_admin' => 1,
        ]);

        Sanctum::actingAs($adminUser, ['*']);

        Storage::fake('print-files');

        $customer    = Customer::factory()->create();
        $typeReceipt = TypeReceipt::factory()->create();
        $file        = UploadedFile::fake()->create('trabajo.pdf', 500, 'application/pdf');

        $payload = [
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Tarjetas de presentación',
            'file_path'       => $file,
            'description'     => 'Trabajo de impresión de prueba',

            // Condicionales: como type_receipt_id != 1, no son obligatorios
            'folio'           => '590',
            'copies_number'   => '2',
            'copies_colors'   => [1,2],

            // Campos obligatorios
            'tint_colors'     => [100, 50, 25, 10],
            'paper_size'      => 1,
            'paper_type'      => 2,
            'quantity'        => 100,
        ];

        $response = $this->postJson(route('print-jobs.store'), $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'No autorizado para crear solicitudes de impresión.',
            ]);

        $this->assertDatabaseCount('print_job_requests', 0);
    }

    public function test_customer_cannot_create_print_job_request_with_invalid_data(): void
    {
        $customerUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($customerUser, ['*']);

        Storage::fake('print-files');

        // Payload mal a propósito con varios errores
        $payload = [
            'name' => '',
        ];

        $response = $this->postJson(route('print-jobs.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_id',
                'type_receipt_id',
                'name',
                'file_path',
                'tint_colors',
                'paper_size',
                'paper_type',
                'quantity',
            ]);

        $this->assertDatabaseCount('print_job_requests', 0);
    }

    public function test_guest_cannot_create_print_job_request(): void
    {
        Storage::fake('print-files');

        $customer    = Customer::factory()->create();
        $typeReceipt = TypeReceipt::factory()->create();
        $file        = UploadedFile::fake()->create('trabajo.pdf', 500, 'application/pdf');

        $payload = [
            'customer_id'     => $customer->id,
            'type_receipt_id' => $typeReceipt->id,
            'name'            => 'Trabajo sin auth',
            'file_path'       => $file,
            'tint_colors'     => [10, 20, 30, 40],
            'paper_size'      => 1,
            'paper_type'      => 2,
            'quantity'        => 5,
        ];

        $response = $this->postJson(route('print-jobs.store'), $payload);

        // ajusta si tu middleware devuelve 403 en vez de 401
        $response->assertStatus(401);

        $this->assertDatabaseCount('print_job_requests', 0);
    }

}
