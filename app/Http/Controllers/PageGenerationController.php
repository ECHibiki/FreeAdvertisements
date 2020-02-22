<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageGenerationController extends Controller
{
	public static function CreateUserFile(string $name){
		file_put_contents("resources/user-json/$name.json", "[{}]");
	}
}
