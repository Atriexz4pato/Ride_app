<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request){
        //validate phone  number
        $request->validate([
            'phone' => 'required|min:10|numeric',
        ]);

        //find or create user model
        $user=User::firstOrCreate([
        'phone_number'=>$request->phone_number
        ]);

        if(!$user){
            return response()->json(['message'=>'Phone number not found'],401);
        }
        //send user OTP

        $user->notify(new LoginNeedsVerification());

        //return a response
        return response()->json(['message'=>'Login code successfully'],200);
    }

    public function verify(Request $request){
        //validate incoming request
    $request->validate([
            'phone_number' => 'required|min:10|numeric',
            'code' => 'required|numeric|between:100000,999999',
        ]);

        //find user
        $user=User::where('phone_number',$request->phone_number)
            ->where('login_code',$request->login_code)
            ->first();

        //check whether code provided matches the one send

        if($user){
            $user->update([
                'login_code'=>null,
            ]);
            return $user->createToken($request->login_code)->plainTextToken;
        }

        //if user not found

        return response()->json(['message'=>'Phone number not found'],401);

    }
}
