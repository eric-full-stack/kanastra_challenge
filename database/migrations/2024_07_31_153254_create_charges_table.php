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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreign('customer_id')->references('government_id')->on('customers');
            $table->string('debt_id')->unique();
            $table->float('debt_amount');
            $table->date('debt_due_date');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('mailed_at')->nullable();
            $table->timestamp('boleto_generated_at')->nullable();
            $table->string('boleto_bar_code')->nullable();
            $table->string('boleto_url')->nullable();
            $table->string('error_message')->nullable();

            $table->datetime('created_at')->useCurrent();
            $table->datetime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
