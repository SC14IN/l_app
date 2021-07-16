<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Mail\TestEmail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function listUsers(Request $request){
        
        return response()->json(['message'=>'working']);
    }





}
