<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // Владелец активации (после появления личных кабинетов).
            // Nullable — старые записи созданы без авторизации.
            $table->foreignId('user_id')
                ->nullable()
                ->after('park_id')
                ->constrained()
                ->nullOnDelete()
                ->comment('Пользователь, загрузивший лог');

            // Путь к загруженному ADIF-файлу (storage/app/private)
            $table->string('adif_path')->nullable()->after('notes');

            // Откуда взялась активация: manual — форма, adif — загрузка лога
            $table->string('source', 10)->default('manual')->after('adif_path');
        });
    }

    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['adif_path', 'source']);
        });
    }
};
