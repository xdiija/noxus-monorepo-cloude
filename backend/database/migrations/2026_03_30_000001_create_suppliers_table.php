<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('inscricao_estadual')->nullable();
            $table->string('email')->unique();
            $table->string('cnpj')->unique();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
