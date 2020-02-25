<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

use App\Ads;
use JWTAuth;
use App\Http\Controllers\PageGenerationController;
class ConfidentialInfoController extends Controller
{

	public function __construct(){
		$this->middleware(['auth:api'])->except('index');
	}

	public function accessInfo(Request $request){
		$name = auth()->user()->name;
		$ad_arr = $this->getUserJson($name);
		return [
		 	'name'=>$name,
			'ads'=> $ad_arr
		];
	}

	public function createInfo(Request $request){
		$name = auth()->user()->name;
		$request->validate([
			'image'=>'required|image|dimensions:min_width=300,max_width=300,min_height=100,max_height=100',
			'url'=>'required|url'
		]);
		$fname = PageGenerationController::StoreAdImage($request->file('image'));
		$this->addUserJSON($name, $fname, $request->input('url'));
		$this->addAdSQL($name, $fname, $request->input('url'));
		return ['created'=>1, 'fname'=>$fname];
	}

	public function removeInfo(Request $request){
		$name = auth()->user()->name;
		$this->removeAdSQL($name, $request->input('uri'), $request->input('url'));
		$this->removeUserJSON($name, $request->input('uri'), $request->input('url'));
		return ['removed'=>1];
	}
	
	public static function addUserJSON(string $name, string $uri, string $url){
		$combined = json_decode(Storage::disk('local')->get("$name.json"), true);
		$combined[] = ['uri'=>$uri, 'url'=>$url];
		Storage::disk('local')->put("$name.json", json_encode($combined));
	}

	public static function removeUserJSON(string $name, string $uri, string $url){
		$combined = json_decode(Storage::disk('local')->get("$name.json"), true);
		$reduced = [];
		foreach($combined as $entry){
			if($entry['uri'] == $uri && $entry['url'] == $url){
				continue;
			}
			else{
				$reduced[] = $entry;
			}
		}
		Storage::disk('local')->put("$name.json", json_encode($reduced));
	}

	public static function getUserJSON(string $name){
		return json_decode(Storage::disk('local')->get("$name.json"), true);
	}

	public static function addAdSQL(string $name, string $uri, string $url){
		$ad = new Ads(['fk_name'=>$name, 'uri'=>$uri, 'url'=>$url]);
		$ad->save();
	}
	
	public static function removeAdSQL(string $name, string $uri, string $url){
		DB::table('ads')->where('fk_name', $name)->where('uri', $uri)->where('url', $url)->delete();
	}

}
