<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Проверяем GET параметр ?lang=en
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, ['ru', 'en'])) {
                Session::put('locale', $locale);
            }
        }

        // Устанавливаем язык из сессии или дефолтный
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        return $next($request);
    }
}
