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
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address');
            $table->text('about');
            $table->double('rating')->default(0.0);
            $table->integer('rating_number')->default(0);
            $table->integer('recommended_number')->default(0);
            $table->bigInteger('consultancies_number')->default(0);
            $table->bigInteger('min_range')->default(0);
            $table->bigInteger('max_range')->default(0);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
