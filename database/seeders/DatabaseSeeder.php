<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\TypeReceipt;
use App\Models\User;
use Illuminate\Container\Attributes\Database;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'id' => 1,
            'username' => 'henryyv',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        DB::table('receipt_categories')->insert([
            ['id' => 1, 'name' => 'Impresión', 'description' => 'Categoría de recibos para servicios de impresión', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Varios', 'description' => 'Categoría de recibos para servicios varios', 'created_at' => now(), 'updated_at' => now()],
        ]);

        TypeReceipt::create([
            'id' => 1,
            'receipt_category_id' => 1,
            'name' => 'Impresion',
            'description' => 'Impresion',
        ]);

        TypeReceipt::create([
            'id' => 2,
            'receipt_category_id' => 2,
            'name' => 'Varios',
            'description' => 'Varios',
        ]);
    }
}
