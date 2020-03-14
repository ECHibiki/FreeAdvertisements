<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

use App\Ads;
use JWTAuth;
use App\Http\Controllers\PageGenerationController;
use App\Http\Controllers\MailSendController;

class ConfidentialInfoController extends Controller
{

	public function __construct(){
		$this->middleware(['auth:api']);
		$this->middleware(['ban:api']);
	}

	public function accessInfo(Request $request){
		$name = auth()->user()->name;
		$ad_arr = array_reverse($this->getUserJson($name));
		return [
			'name'=>"$name",
			'mod'=> auth()->payload()->get("is_mod"),
			'ads'=> $ad_arr
		];
	}

	public function createInfo(Request $request){
		$request->validate([
			'image'=>'required|image|dimensions:width='. env('MIX_IMAGE_DIMENSIONS_W', '500') .',height=' . env('MIX_IMAGE_DIMENSIONS_H', '90'),
			'url'=>['required','url','regex:/^http(|s):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;]+\.[A-Z0-9+&@#\/%=~_|?\-]+$/i']
		]);
		$fname = PageGenerationController::StoreAdImage($request->file('image'));
		$this->addUserJSON($fname, $request->input('url'));
		$this->addAdSQL($fname, $request->input('url'));
		$t = MailSendController::getCooldown();

		if($t < time()){
			$err = MailSendController::sendMail(["name"=>auth()->user()->name, "time"=>date('yMd-h:i:s',time()), "url"=> $request->input('url')],
				['primary_email'=>env('PRIMARY_MOD_EMAIL'), 'secondary_emails'=>env('SECONDARY_MOD_EMAIL_LIST')]);
			MailSendController::updateCooldown();	
			if(!$err){
			    return ['log'=>'Ad Created', 'fname'=>$fname, 'errors'=>'no email']; 
			}
			if (gettype($err) != 'boolean')
				return ['log'=>'Ad Created', 'fname'=>$fname, 'errors'=>$err]; 

		}
		return ['log'=>'Ad Created', 'fname'=>$fname];
	}

	public function removeInfo(Request $request){
		$uri = str_replace("storage/image", "public/image", $request->input('uri'));
		$url = $request->input('url');	
		// slightly dangerous
		if(!ConfidentialInfoController::affirmImageIsOwned($uri)){
			return response(['warn'=>'This banner isn\'t owned'], 401);
		}
		else{
			ConfidentialInfoController::RemoveAdImage($uri);
			$this->removeAdSQL($uri, $url);
			$this->removeUserJSON($uri , $url);
			return response(['log'=>'Ad Removed'], 200);
		}
	}

	public static function addUserJSON(string $uri, string $url){
		$name = auth()->user()->name;
		$combined = json_decode(Storage::disk('local')->get("$name.json"), true);
		$combined[] = ['uri'=>$uri, 'url'=>$url];
			Storage::disk('local')->put("$name.json", json_encode($combined));
	}

	public static function removeUserJSON(string $uri, string $url){
		$name = auth()->user()->name;
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

	public static function getUserJSON(){
		$name = auth()->user()->name;
		return json_decode(Storage::disk('local')->get("$name.json"), true);
	}

	public static function addAdSQL(string $uri, string $url){
		$name = auth()->user()->name;
		$ad = new Ads(['fk_name'=>$name, 'uri'=>$uri, 'url'=>$url, 'ip'=>ConfidentialInfoController::getBestIPSource()]);
		$ad->save();
	}
	
	public static function removeAdSQL(string $uri, string $url){
		$name = auth()->user()->name;
		DB::table('ads')->where('fk_name', $name)->where('uri', $uri)->where('url', $url)->delete();
	}

	// verify if owned
	public static function RemoveAdImage($uri){
		$fname = Storage::delete("$uri");
	}
	public static function affirmImageIsOwned($uri){
		$name = auth()->user()->name;
		return DB::table('ads')->where('fk_name','=', $name)->where('uri','=', $uri)->count() > 0;
	}

	public static function getBestIPSource(){
		return isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : \Request::ip(); 
	}


}
