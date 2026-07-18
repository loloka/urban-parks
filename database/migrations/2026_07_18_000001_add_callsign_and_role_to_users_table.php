<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Основной позывной активатора (уникальный, может быть пустым до заполнения профиля)
            $table->string('callsign', 20)->nullable()->unique()->after('name');
            // Роль: user (активатор/охотник), moderator, admin
            $table->string('role', 20)->default('user')->after('callsign');
        });

        // Все существующие пользователи созданы ДО публичной регистрации — это модераторы/админы.
        // Помечаем их admin, чтобы не потерять доступ в /admin после ограничения панели по роли.
        DB::table('users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['callsign']);
            $table->dropColumn(['callsign', 'role']);
        });
    }
};
