<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Mod;
use App\Ban;
use DB;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserSignInController extends Controller
{

	public function username()
	{
    		return 'name';
	}

	public function loginUser(Request $request){
		$request->validate([
			'name'=>'required',
			'pass'=>'required'
		]);

		$token = $this->returnJWT($request->input("name"), $request->input("pass"), false);
		if(!$token){
			return response()->json(['warn'=>'Username or Password Incorrect'], 401);
		}
		else if($this->checkIfBanned($request->input("name"))){
			return response()->json(['warn'=>'You\'ve been banned...'], 401);
		}
		else{
			$token_arr = [
     		'access_token' => $token,
  			'token_type' => 'bearer',
				'expires_in' => auth()->factory()->getTTL() * 60,
				'log' => "Successfully Logged In"
			];
			return response()->json($token_arr);
		}
	}

	//probably will be unused, but worthwhile fallback
	public function loginMod(Request $request){
		$request->validate([
			'name'=>'required',
			'pass'=>'required'
		]);

		if(!User::query()->where("name", "=", $request->input("name"))->first()->isMod()){
			return response()->json(['warn'=>'You are not a moderator'], 401);
		}

		$token = $this->returnJWT($request->input("name"), $request->input("pass"), true);
		if(!$token){
			return response()->json(['warn'=>'Username or Password Incorrect'], 401);
		}
		else{
			$token_arr = [
		       		'access_token' => $token,
            			'token_type' => 'bearer',
				'expires_in' => auth()->factory()->getTTL() * 60,
				'log' => "Successfully Logged In"
			];
			return response()->json($token_arr);
		}
	}

	public static function returnJWT($name, $pass){
		$token = auth()->attempt(["name"=>$name, "password"=>$pass]);
		return $token;
	}

	public function checkIfBanned($name){
		return Ban::query()
			->where("hardban", "=", "1")
			->where("fk_name", "=", $name)->count() > 0;
	}
}
