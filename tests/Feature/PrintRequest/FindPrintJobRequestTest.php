<?php

namespace Tests\Feature\PrintRequest;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\PrintJobRequest;
use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FindPrintJobRequestTest extends TestCase
{
   use RefreshDatabase;

    public function test_admin_can_get_all_print_jobs(): void
    {
        // Admin
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        // Creamos algunos print jobs (pueden ser de distintos clientes)
        PrintJobRequest::factory()->count(3)->create();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('api/print-jobs'); // ajusta la ruta si tu endpoint es otro

        $response->assertStatus(200);

        // Admin debe ver TODOS los registros
        $response->assertJsonCount(3); // cantidad de elementos en el array raíz
    }

    public function test_customer_sees_only_his_own_print_jobs(): void
    {
        // Usuario cliente (no admin)
        $userCustomer = User::factory()->create([
            'is_admin' => 0,
        ]);

        // Cliente asociado al usuario que hará la petitión
        $customer = Customer::factory()->create([
            'user_id' => $userCustomer->id,
        ]);

        // Otro cliente cualquiera
        $otherCustomer = Customer::factory()->create();

        // Jobs del cliente logueado
        $ownJobs = PrintJobRequest::factory()->count(2)->create([
            'customer_id' => $customer->id,
        ]);

        // Jobs de otro cliente
        $otherJobs = PrintJobRequest::factory()->count(3)->create([
            'customer_id' => $otherCustomer->id,
        ]);

        Sanctum::actingAs($userCustomer, ['*']);

        $response = $this->getJson('api/print-jobs');

        $response->assertStatus(200);

        // El cliente SOLO debe ver sus 2 jobs
        $response->assertJsonCount(2);

        // Aseguramos que los IDs propios están presentes…
        foreach ($ownJobs as $job) {
            $response->assertJsonFragment(['id' => $job->id]);
        }

        // …y los de otros clientes NO aparecen
        foreach ($otherJobs as $job) {
            $response->assertJsonMissing(['id' => $job->id]);
        }
    }
}
