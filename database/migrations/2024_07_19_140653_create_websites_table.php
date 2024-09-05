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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('business_card_id', 6);
            $table->string('site')->nullable();       // Для поля "сайт"
            $table->string('instagram')->nullable();  // Для поля "instagram"
            $table->string('telegram')->nullable();   // Для поля "telegram"
            $table->string('vk')->nullable();         // Для поля "vk"
            $table->timestamps();

            $table->foreign('business_card_id')->references('id')->on('personal_business_cards')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
