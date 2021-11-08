<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

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

        $client = new Client(['verify' => false ]);

        $response = $client->request('POST', url('/') . '/oauth/token', [
            'form_params' => [
                'client_id'     => $clientId, 
                'client_secret' => $clientSecret, 
                'grant_type'    => 'password', 
                'username'      => env('ADMIN_EMAIL'), 
                'password'      => env('ADMIN_PASSWORD'), 
            ]
        ]);

        $results = json_decode($response->getBody()->getContents());

        if (!isset($results->error))
        {
            return $results->access_token;
        }
        else
        {
            return '';
        }  
    }
}
