<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->char('postal_code', 5);
            $table->string('address', 100);
            $table->string('locality_name', 100);
            $table->string('federal_entity', 100);
            $table->string('neighborhood', 64);
            $table->string('municipality', 64);
            $table->string('between_streets', 100)->nullable(false);
            $table->string('interior_number', 10)->nullable(true);
            $table->string('exterior_number', 10)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
