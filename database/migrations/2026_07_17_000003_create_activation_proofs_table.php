<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activation_id')->constrained()->cascadeOnDelete();
            // photo — фото аппаратуры на фоне парка, screenshot — QTHnow, gpx — GPS-трек
            $table->string('type', 20)->default('photo');
            $table->string('path')->comment('Путь в storage (private-диск)');
            $table->string('original_name')->nullable()->comment('Имя файла у пользователя');
            $table->unsignedInteger('size')->nullable()->comment('Размер, байт');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_proofs');
    }
};
