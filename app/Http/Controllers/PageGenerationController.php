<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\DB;

class PageGenerationController extends Controller
{
	public static function CreateUserFile(string $name){
		if(preg_match('/(\\.|\\/|;)/', $name))
			return response(json_encode(["warn"=>"Name has invalid characters"]), 422)->header('Content-Type', 'text/plain');
		Storage::disk('local')->put("$name.json", '[]');
	}

	public static function StoreAdImage($img){
		$fname = Storage::putFile('public/image', $img);
		return $fname;
	}

	public static function getLimitedInfo(){
		$data = (array)PageGenerationController::GetLimitedEntries();
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
		try{
                        $name = auth()->setToken($_COOKIE['freeadstoken'])->user()->name;
                }
                catch(\Exception $e){
                        $name = "";           
		}
                return DB::table('ads')
			->leftJoin('bans', 'ads.fk_name', '=', 'bans.fk_name')
			->whereNull('bans.hardban')
			->orWhere('ip','=', PageGenerationController::getBestIPSource())	
			->select("ads.fk_name", "uri", "url")
			->orderBy('ads.created_at', 'ASC')->inRandomOrder()->first();
	
		}

	public static function GetLimitedEntries($name = null){
		try{
			if(!$name)
                        	$name = auth()->setToken($_COOKIE['freeadstoken'])->user()->name;
                }
                catch(\Exception $e){
                        $name = "";           
		}
		return DB::table('ads')
			->leftJoin('bans', 'ads.fk_name', '=', 'bans.fk_name')
			->when($name == "" || !PageGenerationController::checkBanned($name),function($q){
				return $q->whereNull('bans.hardban')
					->orWhere('ip','=', PageGenerationController::getBestIPSource());
			})
			->select("ads.fk_name", "uri", "url")
			->orderBy('ads.created_at', 'ASC')->get();
                }

	public static function checkBanned($name){
		return  DB::table('bans')->where('fk_name', '=', $name)->count() > 0;
	}

	public static function getBestIPSource(){
		return isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : \Request::ip(); 
	}

}
