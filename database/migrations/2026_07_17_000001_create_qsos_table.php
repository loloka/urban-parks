<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activation_id')->constrained()->cascadeOnDelete()->comment('Активация, из лога которой взято QSO');
            $table->string('callsign', 20)->comment('Позывной корреспондента (охотника)');
            $table->string('station_callsign', 20)->nullable()->comment('Позывной активатора из ADIF (STATION_CALLSIGN/OPERATOR)');
            $table->date('qso_date')->comment('Дата связи (UTC)');
            $table->time('time_on')->comment('Время начала связи (UTC)');
            $table->string('band', 10)->comment('Диапазон: 20M, 40M, 2M...');
            $table->string('mode', 15)->comment('Вид излучения: SSB, CW, FT8...');
            $table->string('submode', 15)->nullable()->comment('Субмода: USB, LSB, MFSK...');
            $table->decimal('freq', 10, 4)->nullable()->comment('Частота, МГц');
            $table->string('rst_sent', 8)->nullable();
            $table->string('rst_rcvd', 8)->nullable();
            $table->string('sig_info', 20)->nullable()->comment('Референс парка корреспондента (park-to-park)');
            $table->timestamps();

            // Дедупликация: одна и та же связь не может попасть в активацию дважды
            $table->unique(
                ['activation_id', 'callsign', 'qso_date', 'time_on', 'band', 'mode'],
                'qsos_dedupe_unique'
            );

            // Главный индекс для автозачёта охотников: ищем по позывному и дате
            $table->index(['callsign', 'qso_date'], 'qsos_hunter_match_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qsos');
    }
};
