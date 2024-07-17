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
            $table->string('first_name')->comment('Имя');
            $table->string('last_name')->comment('Фамилия');
            $table->string('company_name')->nullable()->comment('Название компании');
            $table->string('position')->nullable()->comment('Должность');
            $table->string('photo')->nullable()->comment('Фотография');
            $table->string('phone')->nullable()->comment('Телефон');
            $table->string('telegram')->nullable()->comment('Telegram');
            $table->string('whatsapp')->nullable()->comment('WhatsApp');
            $table->string('email')->nullable()->comment('Email');
            $table->string('qr_code')->nullable()->comment('QR-код');
            $table->string('secondary_phone')->nullable()->comment('Дополнительный телефон');
            $table->string('secondary_email')->nullable()->comment('Дополнительный Email');
            $table->string('address')->nullable()->comment('Адрес');
            $table->string('website')->nullable()->comment('Веб-сайт');
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
