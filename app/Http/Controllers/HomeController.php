<?php

namespace App\Http\Controllers;

use App\Models\User;
use Facade\FlareClient\Http\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $id = Auth::user()->id;
        $users = User::where('id', '!=', $id)->get();
        return view('home', compact('users'));
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        return view('edit', compact('user'));
    }

    public function update($id, Request $request) {
        $request->validate([
            'name' => 'required|string|max:15',
            'email' => 'required|email',
            'phone' => 'required|numeric|max:11',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        $message = 'Status';

        $this->sendMessage($message, $user->phone);

        if($user->update())
            return redirect()->route('edit_user', [$user->id])->with('status', 'Profile updated!');
    }

    public function delete($id) {
        $user = User::findOrFail($id);
        if($user->delete())
            return redirect()->route('home')->with('status', 'Profile deleted!');
    }

    private function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($recipients, 
                ['from' => $twilio_number, 'body' => $message] );
    }
}
