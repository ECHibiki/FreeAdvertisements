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
		$validator = Validator::make($request->all(), [
			'name' => 'required',
			'pass' => 'required'
		]);
                  		
		if($validator->fails()){
			$failed = $validator->failed();
			if(isset($failed['name']))
				return response(json_encode(["created"=>-1]), 401)->header('Content-Type', 'text/plain');
			else
				return response(json_encode(["created"=>-2]), 401)->header('Content-Type', 'text/plain');

		}
		else{
			$code = UserCreationController::addNewUserToDB($request->input('name'), $request->input('pass'));
			if($code == 1){
				PageGenerationController::CreateUserFile($request->input('name'));
				return response(json_encode(["created"=>1]), 200)->header('Content-Type', 'text/plain');
			}
			else{
			 	return response(json_encode(["created"=>0]), 401)->header('Content-Type', 'text/plain');
			}
		}
	}
	
	public static function addNewUserToDB(string $name, string $hashedpass){
		if(!User::where('name', $name)->exists()){
			$user = new User (['name' => $name, 'pass' => bcrypt($hashedpass)]);
			$user->save();
			return 1;
		}
		else
			return 0;

	}
}
