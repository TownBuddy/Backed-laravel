<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth()->user()->is_admin == '1'){
            return $next($request);
        }else{
            return redirect('/home')->with('error','You are not allowed to access that page');
        }
    }
}
