<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use  App\Models\User;

use App\Mail\TestEmail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function listUsers(Request $request){
        $user = auth()->user();
        // return response()->json(['user' => $user]);
        if ($user->role == 'admin'){
            $users = User::select('name','email','role')
                // ->where('role', 'normal')
                ->where('verified', true)
                ->where('deleted', false)
                ->get();
        }
        if ($user->role == 'normal'){
            $users = User::select('name','email')
                ->where('role', 'normal')
                ->where('verified', true)
                ->where('deleted', false)
                ->get();
        }
        return response()->json([$users]);

    }
    public function delSelf(Request $request){
        $user = auth()->user();
        $user->deleted = true;
        $user->deletedBy = 'self';
        $user->save();
        return response()->json(['message'=>'Successfully deleted yourself']);
    }

    public function createUser(Request $request){
        $admin = auth()->user();
        if($admin->'role' == 'admin'){

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
                $user->verified = true;
                $user->deleted = false;
                $user->role = 'normal';
                $user->createdBy = 'admin';
                
                $user->save();
                ]);
                return response()->json(['user' => $user, 'message' => 'Registered Successfully'], 201);
    
            } catch (\Exception $e) {
                return response()->json(['message' => 'User Registration Failed!'], 409);
            }
        }
        return response()->json(['message'=>'Not authorised']);
    }

}
