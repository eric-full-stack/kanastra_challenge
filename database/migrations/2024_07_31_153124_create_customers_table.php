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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            
            // Analisando a base de dados, deduzi que o campo governmentId seria o único campo que precisa ser único, pois é o campo que identifica o cliente.
            $table->unsignedBigInteger('government_id')->unique(); 

            $table->datetime('created_at')->useCurrent();
            $table->datetime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
