<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parks', function (Blueprint $table) {
            // Добавляем коды страны и региона
            $table->string('country_code', 5)->after('reference')->default('RU');
            $table->string('region_code', 10)->after('country_code')->nullable();

            // Английские версии
            $table->string('name_en')->after('name')->nullable();
            $table->text('description_en')->after('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('parks', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'region_code', 'name_en', 'description_en']);
        });
    }
};
