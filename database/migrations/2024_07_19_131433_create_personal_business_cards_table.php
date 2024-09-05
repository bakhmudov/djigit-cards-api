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
        Schema::create('personal_business_cards', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('fio');
            $table->string('about_me')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_position');
            $table->string('photo')->nullable();
            $table->json('main_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_business_cards');
    }
};
