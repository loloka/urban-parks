<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Модераторы и админы созданы до включения подтверждения email —
     * они доверенные, помечаем их email подтверждённым, чтобы не требовать
     * верификацию для доступа к загрузке активаций.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['moderator', 'admin'])
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Необратимо по смыслу — оставляем как есть.
    }
};
