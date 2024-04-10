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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->foreignId('date_id')->constrained('expert_dates')->onDelete('cascade');
            $table->foreignId('communication_type_id')->constrained('communication_types')->onDelete('cascade');
            $table->tinyInteger('status')->default(0);
            $table->text('problem');
            $table->text('response')->nullable();
            $table->double('rating')->default(0.0);
            $table->boolean('is_recommended')->default(0);
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
