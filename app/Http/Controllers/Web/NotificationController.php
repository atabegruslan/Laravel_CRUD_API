<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Store the PushSubscription.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Getting data from Subscription object
        $this->validate($request, [
            'endpoint'    => 'required', // Unique to every client
            'keys.auth'   => 'required', // Authentication secret
            'keys.p256dh' => 'required'  // Elliptic-curve Diffie-Hellman public key - for message encryption
        ]);

        $endpoint = $request->endpoint;
        $token    = $request->keys['auth'];
        $key      = $request->keys['p256dh'];
        $user     = Auth::user();

        $user->updatePushSubscription($endpoint, $key, $token);
        
        return response()->json(['success' => true], 200);
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
}
