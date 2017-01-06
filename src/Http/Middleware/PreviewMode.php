<?php

namespace Bozboz\Jam\Http\Middleware;

use Illuminate\Support\Facades\Config;

class PreviewMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, $next, $guard = null)
    {
        if ($request->get('p') === md5(date('ymd'))) {
            Config::set('jam.preview-mode', true);
        }

        return $next($request);
    }
}