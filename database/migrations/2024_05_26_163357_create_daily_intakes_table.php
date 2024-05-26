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
        Schema::create('daily_intakes', function (Blueprint $table) {
            $table->id();
            $table->integer('max_calories')->default(0);
            $table->integer('daily_calories')->default(0);
            $table->integer('daily_sugar')->default(0);
            $table->integer('daily_fat')->default(0);
            $table->integer('daily_protein')->default(0);
            $table->integer('daily_carbohydrate')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_intakes');
    }
};
