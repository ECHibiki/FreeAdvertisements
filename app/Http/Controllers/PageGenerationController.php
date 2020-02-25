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
		$fname = Storage::putFile('image', $img, 'public');
		return $fname;
	}
	public static function RemoveAdImage($uri){
		$fname = Storage::delete("$uri");
	}
	public function GenerateAdPage(){
		$rand_ad = $this->GetRandomAdEntry();
		if($rand_ad == null){
			return "asdf";
		}
		else
			return view('banner', ['url'=>$rand_ad->url, 'uri'=>$rand_ad->uri, 'name'=>$rand_ad->fk_name]);
	}

	public static function GetRandomAdEntry(){
		return 	DB::table('ads')->inRandomOrder()->first();
	}
}
