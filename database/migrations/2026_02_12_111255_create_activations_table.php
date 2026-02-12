<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('park_id')->constrained()->cascadeOnDelete()->comment('ID парка');
            $table->string('callsign')->comment('Позывной активатора');
            $table->date('activation_date')->comment('Дата активации');
            $table->integer('qso_count')->default(0)->comment('Количество QSO');
            $table->text('notes')->nullable()->comment('Заметки');
            $table->timestamps();
            
            // Индексы
            $table->index('callsign');
            $table->index('activation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};