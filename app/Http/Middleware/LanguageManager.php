<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App;

class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        }
        if ($request->has('lang') && !str_contains($request->url(), 'lang/change')){
            $available_locales = ['en', 'bg', 'ro', 'es'];
            foreach($available_locales as $locale) {
                if ($locale === $request->lang){
                    App::setLocale($locale);
                    session()->put('locale', $locale);
                    return redirect()->to($request->fullUrlWithoutQuery('lang'));
                }
            }
        }
        return $next($request);
    }
}
