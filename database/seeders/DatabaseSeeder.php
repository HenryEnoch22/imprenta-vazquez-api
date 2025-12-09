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

        TypeReceipt::create([
            'id' => 1,
            'receipt_category' => 1,
            'name' => 'Impresion',
            'description' => 'Impresion',
        ]);

        TypeReceipt::create([
            'id' => 2,
            'receipt_category' => 2,
            'name' => 'Varios',
            'description' => 'Varios',
        ]);
    }
}