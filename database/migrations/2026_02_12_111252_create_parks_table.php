<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parks', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->comment('Референс парка: UP-0001'); 
            $table->string('name')->comment('Название парка');
            $table->string('city')->comment('Город');
            $table->string('region')->comment('Регион/область');
            $table->decimal('latitude', 10, 7)->comment('Широта');
            $table->decimal('longitude', 10, 7)->comment('Долгота');
            $table->text('description')->nullable()->comment('Описание парка');
            $table->string('area')->nullable()->comment('Площадь (га)');
            $table->enum('status', ['active', 'pending', 'inactive'])->default('active')->comment('Статус');
            $table->integer('activation_count')->default(0)->comment('Количество активаций');
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('city');
            $table->index('region');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parks');
    }
};