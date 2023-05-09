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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('year');
            $table->string('type');
            # This will add the columns 'created_at' and 'updated_at' required for Laravel
            $table->timestamps();           
            $table->integer('total_rates')->default(0);
            $table->integer('sum_rates');
            $table->string('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
