<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    protected $supported_languages = ['en', 'hi'];

    public function handle($request, Closure $next)
    {
        
        if(!session()->has('locale')) {
            session(['locale' => $request->getPreferredLanguage($this->supported_languages)]);
        }

        app()->setLocale(session('locale'));
        return $next($request);
    }
}
