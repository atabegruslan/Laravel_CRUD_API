<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use Session;

class MiscController extends Controller
{
    public function contactform()
    {
        return view('contact.form');
    }

    public function contact(Request $request)
    {
        $this->validate($request, [
            'name'    => 'required|max:40',
            'email'   => 'required|email|max:40',
            'subject' => 'required|max:40',
            'body'    => 'required|max:200'
        ]); 

        $data = array(
            'name'    => $request->name,
            'email'   => $request->email,
            'subject' => $request->subject,
            'body'    => $request->body
        );

        Mail::send(
            'contact.email',
            $data, 
            function($message) use ($data) 
            {
                $message->from( $data['email'] );
                $message->to(env('ADMIN_EMAIL'))->subject( $data['subject'] );
            }
        );

        Session::flash('success', 'Email Sent');

        return redirect('entry');
    }
}
