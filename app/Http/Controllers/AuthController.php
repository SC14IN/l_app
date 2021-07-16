<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use  App\Models\User;
use  App\Models\VerifyUser;
use  App\Models\ForgotPassword;

use App\Mail\TestEmail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    public function register(Request $request){
        //validate incoming request 
        //if email already registered and not verified, register again and give new token for
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
            $user->verified = false;
            $user->deleted = false;
            $user->role = 'normal';
            $user->createdBy = 'self';
            
            $user->save();
            
            $verify = new VerifyUser;
            $verify->user_id = $user->id;
            $verify->token = $this->generateToken(16);
            $verify = VerifyUser::create([
                'user_id' => $user->id,
                'token' => $this->generateToken(16)
            ]);
            
            // $data = ['name' => $user->name,'token'=>$verify->token];
            // Mail::to($user->email)->send(new TestEmail($data));

            // return response()->json(['user' => $user, 'message' => 'Registered Successfully.']);
            // //return successful response
            return response()->json(['user' => $user, 'message' => 'Registered Successfully. Verification token: '.($verify->token).' sent to mail'], 201);

        } catch (\Exception $e) {
            // return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }
    public function emailVerify(Request $request){
        $token = $request->input('token');
        $verify = VerifyUser::where('token',$token)->first();
        if($verify){
            $user = User::find($verify->user_id);
            $user->verified = true;
            $user->save();
            return response()->json(['message'=>'User verified successfully']);
        }
        return response()->json(['message'=>'Token invalid or expired']);

    }
    public function login(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        $user = User::where('email',$credentials['email'])->first();
        if(!$user->verified){return response()->json(['message' => 'Please verify your email'], 401);}
        if($user->deleted){return response()->json(['message' => 'account deleted by '.($user->deletedBy)], 401);}
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    public function forgotPassword(Request $request){
        $this->validate($request, [
            // 'name' => 'required|string',
            'email' => 'required|email',
            // 'password' => 'required|confirmed',
        ]);
        $credentials = $request->only(['email']);
        $user = User::where('email',$credentials['email'])->first();
        if(!$user){
            return response()->json(['message'=>'Email not registered']);
        }
        if(!$user->verified){return response()->json(['message'=>'Email not Verified']);
        }
        $forgotpass = ForgotPassword::firstornew(['user_id'=> $user->id]);
        $forgotpass->token = $this->generateToken(16);
        $forgotpass->save();
        ///////del token
        //send mail the generated token
        return response()->json(['user' => $user, 
        'token' => 'Password Verification token: '.($forgotpass->token).'sent to mail'], 201);
 
    }
    public function resetPassword(Request $request){
        $this->validate($request, [
            'token' => 'required|string',
            // 'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);
        $token = $request->input('token');
        $verify = ForgotPassword::where('token',$token)->first();
        //email input not used
        if($verify){
            $user = User::find($verify->user_id);
            if(!$user->verified){return response()->json(['message'=>'Email not Verified']);
            }
            $user->password = app('hash')->make($request->input('password'));
            $user->save();
            return response()->json(['message'=>'Password reset successfully']);
        }
        return response()->json(['message'=>'Token invalid or expired']);

    }
}