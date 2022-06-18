<?php

namespace App\Http\Middleware;
 
use Closure;
use App\Domains\Auth\Models\ApiKeys;

class EnsureTokenIsValid
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
       
        $token = self::get_token($request);
        $apiKey = ApiKeys::where('key', $token)->first();
        
        if ($apiKey && $apiKey->status == 1) {
            return $next($request);
        }else{

            $error = 'Invalid token';
            $response = [
                'success' => false,
                'message' => $error,
            ];
    
            return response()->json($response, 400);

        }
        
    }

    public static function get_token($request){
        $token = '';
           
        // bearer token
        $token = $request->bearerToken();
          
        if (!$token){
           // api key token authorization
          $token = $request->header('token');
          if (!$token){
              // api key token authorization
               $token = $request->header('token');
               if (!$token)
                  // request parameter
                  $token =$request->input('token') ;
          }
        }
    
        return $token;
          
    
    }
    
}
