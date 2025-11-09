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
        Schema::create('type_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_category_id')->constrained('receipt_categories')->onDelete('cascade'); //Todo: ver que es lo más recomendable aquí
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_receipts');
    }
};
