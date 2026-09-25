<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Auth;
use Hash;

class AuthController extends Controller
{
    use ApiResponse;
public function register(RegisterRequest $request){
$data=$request->validated();
$user=User::create([
    'name'=>$data['name'],
    'email'=>$data['email'],
    'password'=>Hash::make($data['password'])
]);
$token=$user->createToken('auth_token')->plainTextToken;
    return Self::success("user Created Successfully",[
        'user'=>new UserResource($user),
        'token'=>$token
        ],201);

}

public function login(LoginRequest $request){
    $data=$request->validated();

    $user=User::where('email',$data['email'])->first();
    if(!$user ||! Hash::check($data['password'], $user->password)){
        return self::error("Invalid Credentials",null,401);
    }
    $token=$user->createToken('auth_token')->plainTextToken;
        return Self::success("Login successfully",[
        'user'=>new UserResource($user),
        'token'=>$token
        ]);
}
public function logout(){
    $user=auth()->user();
    $token=$user->currentAccessToken();
    $token->delete();
    return Self::success("logout successfully");
}
}
