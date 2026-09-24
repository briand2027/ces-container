<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'it');
        $available = array_keys(config('locales.available', ['it' => []]));

        if (! in_array($locale, $available, true)) {
            $locale = 'it';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
