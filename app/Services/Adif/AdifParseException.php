<?php

namespace App\Services\Adif;

use RuntimeException;

/**
 * Ошибка разбора ADIF-файла (невалидный файл или запись).
 */
final class AdifParseException extends RuntimeException {}
