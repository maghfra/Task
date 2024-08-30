<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $verification_code = mt_rand(10000,99999);

        $user = User::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'verification_code'=>$verification_code
        ]);

        Log::info("Verification code for {$user->phone}: {$verification_code}");

        $token = $user->createToken('auth_user')->plainTextToken;

        return response()->json(['message'=>'user registerd successfully','Token'=>$token,'user data'=>$user]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'verification_code'=>'required'
        ]);

        $user = User::where('verification_code',$request->verification_code)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid verification code'], 422);
        }   
        $user->update([
            'is_verified' => true,
            'verification_code' => null
        ]);
        
        return response()->json(['message' => 'Account verified successfully']);         
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        if (!$user->is_verified) {
            return response()->json(['message' => 'Account not verified'], 403);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json(['message => User login Successfully','access_token' => $token,'user' => $user,]);
    }
}
