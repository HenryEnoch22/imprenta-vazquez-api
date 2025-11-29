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
        Schema::table('print_job_requests', function (Blueprint $table) {
            $table->string('name')->after('type_receipt_id');
            $table->text('file_path')->after('name');
            $table->text('description')->after('file_path');
            $table->text('folio')->nullable()->after('description');
            $table->string('paper_size')->after('folio');
            $table->integer('copies_number')->nullable()->after('paper_size');
            $table->json('copies_colors')->nullable()->after('copies_number');
            $table->json('tint_colors')->nullable()->after('copies_colors');
            $table->integer('paper_type')->after('tint_colors');
            $table->integer('quantity')->after('paper_type');
            $table->integer('status')->default(1)->after('quantity');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_job_requests', function (Blueprint $table) {
            $table->dropColumn([
                'category_id',
                'file_path',
                'description',
                'folio',
                'paper_size',
                'copies_number',
                'copies_colors',
                'tint_colors',
                'paper_type',
                'quantity',
                'status',
                'deleted_at',
            ]);
        });
    }
};
