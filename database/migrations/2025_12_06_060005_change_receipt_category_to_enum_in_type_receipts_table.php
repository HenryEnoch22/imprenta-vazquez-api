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
        Schema::table('type_receipts', function (Blueprint $table) {
            // 1) Quitar la foreign key y la columna receipt_category_id
            $table->dropForeign(['receipt_category_id']);
            $table->dropColumn('receipt_category_id');

            // 2) Agregar el nuevo campo enum receipt_category
            $table->enum('receipt_category', [
                'Impresión',
                'Varios',
            ])->default('Impresión')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_receipts', function (Blueprint $table) {
            // 1) Eliminar el enum
            $table->dropColumn('receipt_category');

            // 2) Volver a crear la columna original con su foránea
            $table->foreignId('receipt_category_id')
                ->constrained('receipt_categories')
                ->onDelete('cascade');
        });
    }
};
