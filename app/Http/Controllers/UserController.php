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
    public function delUser(Request $request){
        $admin = auth()->user();
        if ($admin->role == 'admin'){
            $this->validate($request, [
                'email' => 'required|email',
            ]);
            $user = User::where('email',$request->input('email'))->first();
            $user->deleted = true;
            $user->deletedBy = 'admin';
            $user->save();
            return response()->json(['message'=>'Successfully deleted user']);

            // return response()->json(['message'=>$request->input('email')]);
        }
        return response()->json(['message'=>'Not authorised']);
    }
    public function createUser(Request $request){
        $admin = auth()->user();
        if ($admin->role == 'admin'){

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
                return response()->json(['email' => $user->email,'password'=>$plainPassword, 'message' => 'Registered Successfully'], 201);
    
            } catch (\Exception $e) {
                return response()->json(['message' => 'User Registration Failed!'], 409);
            }
        }
        return response()->json(['message'=>'Not authorised']);
    }
/////what to do if we want to re create a deleted account
//can i delete the prevoius account from db?

// $products = Product::where('name_en', 'LIKE', '%'.$search.'%')->get();
// $q->where('created_at', '>=', date('Y-m-d').' 00:00:00'));
// $q->whereDate('created_at', '=', date('Y-m-d'));
    
    public function filter(Request $request){
        $admin = auth()->user();
        $column = $request->input('column');
        $string = $request->input('string');

        //take the input for what column i want to filter by
        //string to filter
        if ($admin->role == 'admin'){
            if ($column == 'name' || $column == 'email'){
                $users = User::select('name','email','role')
                    ->where($column, 'LIKE', '%'.$string.'%')
                    ->where('verified', true)
                    ->where('deleted', false)
                    ->get();
                return $users;
            }
            if($column == 'role'){
                $users = User::select('name','email')
                    ->where('role', $string)
                    ->where('verified', true)
                    ->where('deleted', false)
                    ->get();
                return $users;
            }
            if($column == 'deleted'){
                $users = User::select('name','email')
                    ->where('verified', true)
                    ->where('deleted', true)
                    ->get();
                return $users;
            }
            if($column == 'verified'){
                $users = User::select('name','email','role','deleted')
                    ->where('verified', true)
                    ->get();
                return $users;
            }
            if($column == 'date'){
                $users = User::select('name','email','role')
                    ->whereDate('created_at', '=', $string)
                    ->where('verified', true)
                    ->where('deleted', false)
                    ->get();
                return $users;
            }
            if($column == 'createdBy'){
                $users = User::select('name','email','role')
                    ->where('createdBy', $string)
                    ->where('verified', true)
                    ->where('deleted', false)
                    ->get();
                return $users;
            }
            if($column == 'deletedBy'){
                $users = User::select('name','email','role')
                    ->where('deletedBy', $string)
                    ->where('verified', true)
                    ->where('deleted', true)
                    ->get();
                return $users;
            }
            if($column == 'id'){
                $users = User::select('name','email','role')
                    ->where('id', $string)
                    ->get();
                return $users;
            }
            
        }
        return response()->json(['message'=>'Not allowed']);
    }

}