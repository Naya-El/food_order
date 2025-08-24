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
        Schema::create('standard_items', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->enum('type',['food','drink']);
            $table->text('description');
            $table->decimal('price');
            $table->string('image');
            $table->boolean('is_available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_items');
    }
};
