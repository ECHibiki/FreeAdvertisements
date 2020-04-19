<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

use App\Ad;
use App\AntiSpam;
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

	public function doAntiSpam($name){
		return DB::table('antispam')
			->where('name','=',$name)
			->where('unix', '>=',
				Carbon::now()->subSeconds(intval(env('COOLDOWN',60)))->timestamp);
	}
	public function updateAntiSpam($name){
		DB::table('antispam')
			->where('unix', '<',
				Carbon::now()->subSeconds(intval(env('COOLDOWN',60)))->timestamp)
			->delete();
		AntiSpam::create(['name'=>$name, 'unix' => 	Carbon::now()->timestamp]);
	}

	public function createInfo(Request $request){
		$response ="";
		$name = auth()->user()->name;
		$antispam_response = $this->doAntiSpam($name);
	  if ($antispam_response->count() > 0){
			return ['warn'=>'posting too fast('.
				($antispam_response->first()->unix - Carbon::now()->subSeconds(intval(env('COOLDOWN',60)))->timestamp) . ' seconds)'];
		}
		else{
			if($request->input('size') == "small"){
				$response = $this->createSmallInfo($request);
			}
			else{
				$response = $this->createWideInfo($request);
			}
		}
		$this->updateAntiSpam($name);
		return $response;
	}

	public function createSmallInfo(Request $request){
		$request->validate([
			'image'=>'required|image|dimensions:width='. env('MIX_IMAGE_DIMENSIONS_SMALL_W', '300') .',height=' . env('MIX_IMAGE_DIMENSIONS_SMALL_H', '140'),
		]);
		$fname = PageGenerationController::StoreAdImage($request->file('image'));
		$this->addUserJSON($fname, env('MIX_APP_URL', 'https://kissu.moe'));
		$this->addAdSQL($fname, env('MIX_APP_URL', 'https://kissu.moe'), 'small');
		$t = MailSendController::getCooldown();

		if($t < time()){
			$err = MailSendController::sendMail(["name"=>auth()->user()->name, "time"=>date('yMd-h:i:s',time()), "url"=> $request->input('url'), 'fname'=>$fname],
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

	public function createWideInfo(Request $request){
		$request->validate([
			'image'=>'required|image|dimensions:width='. env('MIX_IMAGE_DIMENSIONS_W', '500') .',height=' . env('MIX_IMAGE_DIMENSIONS_H', '90'),
			'url'=>['required','url','regex:/^http(|s):\/\/[A-Z0-9+&@#\/%?=~_|!:,.;]+\.[A-Z0-9+&@#\/%=~_|?\-]+$/i']
		]);
		$fname = PageGenerationController::StoreAdImage($request->file('image'));
		$this->addUserJSON($fname, $request->input('url'));
		$this->addAdSQL($fname, $request->input('url'), 'wide');
		$t = MailSendController::getCooldown();

		if($t < time()){
			$err = MailSendController::sendMail(["name"=>auth()->user()->name, "time"=>date('yMd-h:i:s',time()), "url"=> $request->input('url'), 'fname'=>$fname],
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

	public static function addAdSQL(string $uri, string $url, string $size='wide'){
		$name = auth()->user()->name;
		$ad = new Ad(['fk_name'=>$name, 'uri'=>$uri, 'url'=>$url, 'ip'=>ConfidentialInfoController::getBestIPSource(), 'size'=>$size]);
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
