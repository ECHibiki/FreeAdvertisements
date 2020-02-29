<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
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

		$token = $this->returnJWT($request->input("name"), $request->input("pass"));
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
		$token = JWTAuth::attempt(["name"=>$name, "password"=>$pass]);
		
		//var_dump( $token);
		return $token;
	}

}
