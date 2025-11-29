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
        Schema::create('print_job_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_job_request_id')->constrained('print_job_requests')->onDelete('cascade');
            $table->integer('payment_method');
            $table->decimal('amount', 7, 2);
            $table->date('paid_at');
            $table->text('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_job_payment');
    }
};
