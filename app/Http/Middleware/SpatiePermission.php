<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SpatiePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $obj = Route::current()->getAction()['as'];

        if ( !auth()->user()->can($obj) )
        {
            abort(403);
        }

        return $next($request);
    }
}
