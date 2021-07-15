<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\User;

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    
    public function register(Request $request){
        //validate incoming request 
        // $data = ['message' => 'This is a test!'];
        // Mail::to('ch.sathvik@vmock.com')->send(new TestEmail($data));

        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        try {

            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();
            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }

    }
    public function login(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    // public function logout(){
    //     Auth::logout();
    //     return response()->json(['message' => 'User Logged out!'], 201);
    // }

}