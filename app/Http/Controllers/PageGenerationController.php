<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\DB;

class PageGenerationController extends Controller
{
	public static function CreateUserFile(string $name){
		Storage::disk('local')->put("$name.json", '[]');
	}

	public static function StoreAdImage($img){
		$fname = Storage::putFile('public/image', $img);
		return $fname;
	}
	public static function RemoveAdImage($uri){
		$fname = Storage::delete("$uri");
	}
	public static function getAllInfo(){
		$data = (array)PageGenerationController::GetAllEntries();
		$data = array_reverse(array_pop($data));
		return json_encode($data);
	}

	public function GenerateAdPage(){
		$rand_ad = $this->GetRandomAdEntry();
		if($rand_ad == null){
			return "asdf no ads";
		}
		else
			return view('banner', ['url'=>$rand_ad->url, 'uri'=>str_replace('public','storage',$rand_ad->uri), 'name'=>$rand_ad->fk_name]);
	}
	public function GenerateAdJSON(){
		$rand_ad = $this->GetRandomAdEntry();
		if($rand_ad == null){
			return "asdf no ads";
		}
		else
			return json_encode([['url'=>$rand_ad->url, 'uri'=>str_replace('public','storage',$rand_ad->uri), 'name'=>$rand_ad->fk_name]]);

	}

	// banned users will not show up in rotation
	public static function GetRandomAdEntry(){
		return  DB::table('ads')->
                        leftJoin('bans', 'ads.fk_name', '=', 'bans.fk_name')->whereNull('bans.hardban')->select("ads.fk_name", "uri", "url")->inRandomOrder()->first();
	}

        public static function GetAllEntries(){
                return DB::table('ads')->orderBy('created_at', 'ASC')->get();
	}

	public static function affirmImageIsOwned($name, $uri){
		return DB::table('ads')->where('fk_name','=', $name)->where('uri','=', $uri)->count() > 0;
	}
}
