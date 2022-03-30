<?php

namespace App\Http\Middleware;

use Closure;
use App\Users_Model;

class CheckApiToken
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
        $token = str_replace('Bearer ','',$request->header('Authorization'));
        $tokenHash = hash('sha256', $token);

        $user = Users_Model::where('api_token',$tokenHash)->first();
        if($user != null){
            return $next($request);
        }else{
            return response()->json(['status'=>false,'status_code'=>'404','message'=>'Your Account Is Blocked','error'=>['Your Account Is Blocked']], 200);
        }
    }
}
