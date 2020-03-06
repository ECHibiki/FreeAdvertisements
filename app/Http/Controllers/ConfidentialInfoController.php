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
		$this->middleware(['auth:api']);
	}

	public function accessInfo(Request $request){
		$name = auth()->user()->name;
		$ad_arr = $this->getUserJson($name);
		return [
			'name'=>$name,
			'mod'=> auth()->payload()->get("is_mod"),
			'ads'=> $ad_arr
		];
	}

	public function createInfo(Request $request){
		$name = auth()->user()->name;
		$request->validate([
			'image'=>'required|image|dimensions:width=500,height=90',
			'url'=>['required','url','regex:/^http(|s):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;]+\.[A-Z0-9+&@#\/%=~_|]+$/i']
		]);
		$fname = PageGenerationController::StoreAdImage($request->file('image'));
		$this->addUserJSON($name, $fname, $request->input('url'));
		$this->addAdSQL($name, $fname, $request->input('url'));
		return ['log'=>'Ad Created', 'fname'=>$fname];
	}

	public function removeInfo(Request $request){
		$name = auth()->user()->name;
		$uri = str_replace("storage/image", "public/image", $request->input('uri'));
		$url = $request->input('url');	
		if(!PageGenerationController::affirmImageIsOwned($name, $uri)){
			return ['warn'=>'This banner isn\'t owned'];
		}
		$this->removeAdSQL($name, $uri, $url);
		$this->removeUserJSON($name, $uri , $url);
		PageGenerationController::RemoveAdImage($uri);
		return ['log'=>'Ad Removed'];
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
