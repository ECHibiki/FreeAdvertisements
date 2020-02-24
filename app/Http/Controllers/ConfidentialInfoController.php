<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfidentialInfoController extends Controller
{

	public function __construct(){
		$this->middleware(['auth:api'])->except('index');
	}

	public function accessInfo(Request $request){
		return response("asd",200);
	}
}
