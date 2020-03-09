<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserCreationController extends Controller
{



	public function createNewUser(Request $request){
		$request->validate([
			'name' => 'required|max:30',
			'pass' => 'required|confirmed|min:5'
		]);
                  		

		$response_msg = UserCreationController::addNewUserToDB($request->input('name'), $request->input('pass'));
		if($response_msg === 1){
			if($err = PageGenerationController::CreateUserFile($request->input('name')))
				return $err;
			return response(json_encode(["log"=>"Successfully Created"]), 200)->header('Content-Type', 'text/plain');
		}
		else{
			return $response_msg;
		}
	
	}
	
	public static function addNewUserToDB(string $name, string $hashedpass){
		if(preg_match('/(\\.|\\/|;)/', $name)){
			return response(json_encode(["warn"=>"Name has invalid characters"]), 422)->header('Content-Type', 'text/plain');
		}
		if(!User::where('name', $name)->exists()){
			$user = new User (['name' => $name, 'pass' => bcrypt($hashedpass)]);
			$user->save();
			return 1;
		}
		else
			return response(json_encode(["warn"=>"Username Already Exists"]), 401)->header('Content-Type', 'text/plain');

	}
}
