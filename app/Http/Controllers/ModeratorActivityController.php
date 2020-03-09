<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Bans;
use App\Ads;
use App\Mods;
use App\Http\Controllers\ConfidentialInfoController;
use App\Http\Controllers\PageGenerationController;
use Storage;
class ModeratorActivityController extends Controller
{
	public function __construct(){
		$this->middleware(['auth:api']);
		$this->middleware(['mod:api']);
	}

	public static function getAllInfo(){
		$data = (array)ModeratorActivityController::GetAllEntries();
		$data = array_reverse(array_pop($data));
		return json_encode($data);
	}

	public function banUser(Request $request){
		$request->validate([
			"target"=>"required|string",
			"hard"=>"required|boolean"
		]);
		$hard = ($request->input("hard"));
		$target = ($request->input("target"));
		
		$this->createNewBan($request->input("target"), $request->input("hard"));
		if($hard == 1) 
			$hard = "hard";
		else 
			$hard = "soft";
		return response(json_encode(["log"=>"user $target was $hard banned"]), 200);
	}

	public function deleteAll(Request $request){
		$request->validate([
			"name"=>"required|string"
		]);
		$target = $request->input("name");
		$this->removeAllBanners($target);
		return response(json_encode(["log"=>"user $target was purged"]), 200);

	}

	public function deleteIndividual(Request $request){
		$request->validate([
			"name"=>"required|string",
			"uri"=>"required|string",
			"url"=>"required|string"
		]);
		$name = $request->input("name");
		$uri = str_replace("storage/image/", "public/image/", $request->input("uri"));
		ModeratorActivityController::removeIndividualBannerFromJSON($name, $uri, $request->input("url"));
		ModeratorActivityController::removeIndividualBannerFromImages($uri);
		ModeratorActivityController::removeIndividualBannerFromDB($uri);
		return response(json_encode(["log"=>"$name's image was pruned"]), 200);
 

	}

	public static function GetAllEntries(){
                return DB::table('ads')->orderBy('created_at', 'ASC')->get();
	}

	public function createNewBan($target, $hard){
		if(ModeratorActivityController::GetBanInfo($target)){}
		else{
			$ban = new Bans();
		}
		$ban->fk_name = $target;
		$ban->hardban = $hard;
		$ban->save();
	}

	public function removeAllBanners($target){
		Ads::where('fk_name', '=', $target)->delete();
		$all = ConfidentialInfoController::getUserJson($target);
		foreach ($all as $ad){
			PageGenerationController::RemoveAdImage($ad['uri']);
		}
		$this->truncateUserJSON($target);

	}

	public static function removeIndividualBannerFromJSON($name, $uri, $url){
		ConfidentialInfoController::removeUserJSON($name,$uri,$url);
	}

	public static function removeIndividualBannerFromDB($uri){
		Ads::where('uri', '=', $uri)->delete();
	}

	public static function removeIndividualBannerFromImages($uri){
		Storage::delete($uri);
	}

	public static function GetBanInfo($name){
		return Bans::query()->where('fk_name', '=', $name)->first();
	}

	public static function createMod($name){
		$mod = new Mods(['fk_name'=>$name]);
		$mod->save();
	}

	public function truncateUserJSON($target){
		Storage::disk('local')->put("$target.json","[]");
	}
}
