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
        Schema::create('expert_communications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cost_appointment');
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->foreignId('communication_type_id')->constrained('communication_types')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_communications');
    }
};
