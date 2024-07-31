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
        Schema::create('employee_business_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_business_card_id')->constrained()->onDelete('cascade');
            $table->string('photo')->nullable();
            $table->string('fio');
            $table->string('job_position');
            $table->json('main_info')->nullable();
            $table->json('phones')->nullable();
            $table->json('emails')->nullable();
            $table->json('addresses')->nullable();
            $table->json('websites')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_business_cards');
    }
};
