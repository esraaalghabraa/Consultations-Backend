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
        Schema::create('communication_type_expert', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->foreignId('communication_type_id')->constrained('communication_types')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_type_expert');
    }
};
