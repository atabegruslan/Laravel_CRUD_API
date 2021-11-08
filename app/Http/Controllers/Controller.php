<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $currentUser;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            
            $this->currentUser = $request->user();

            view()->share([
                'currentUser' => $this->currentUser,
                'token'       => $this->getAccessToken($this->currentUser),
                //'token'       => auth('api')->login($this->currentUser), // This would would work for JWT, but not for Passport
            ]);

            return $next($request);
        });
    }

    private function getAccessToken($user)
    {
        $clientId = 2;

        $clientSecret = DB::table('oauth_clients')
            ->where('id', $clientId)
            ->first()
            ->secret;

        $curl = curl_init(); 

        $payload = [    
            'client_id'     => $clientId, 
            'client_secret' => $clientSecret, 
            'grant_type'    => 'password', 
            'username'      => 'ruslanaliyev1849@gmail.com', 
            'password'      => '12345678', 
        ];  

        curl_setopt_array($curl, array( 
            CURLOPT_URL            => url('/') . '/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $payload,  
        )); 

        $response = curl_exec($curl);
        $response = json_decode($response);   

        curl_close($curl);

        if (!isset($response->error))
        {
            return $response->access_token;
        }
        else
        {
            return '';
        }  
    }
}
