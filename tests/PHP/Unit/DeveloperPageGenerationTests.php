<?php

namespace Tests\Unit;



use App\Http\Controllers\ConfidentialInfoController;


use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use App\Bans;

class DeveloperPageGenerationTests extends TestCase
{

	use RefreshDatabase;

//routes

   public function test_data_addition_route(){
	   $response = $this->withHeaders(['Accept' => 'application/json'])->post("api/details", []);
	   $response->assertStatus(401);
   }
  
   public function test_data_access_route(){
	   $response = $this->withHeaders(['Accept' => 'application/json'])->get("api/details", []);
	   $response->assertStatus(401);
   }

   public function test_user_all_route(){
	   $_SERVER["HTTP_X_REAL_IP"] = "1";
	   $response = $this->withHeaders(['Accept' => 'application/json'])->get("api/all", []);
	   $response->assertStatus(200);
   }
//image

   public function test_upload_image_to_mock_storage(){
	   Storage::fake('image');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$fname = \app\Http\Controllers\PageGenerationController::StoreAdImage($img);
	Storage::assertExists($fname);

	return $fname;
   }
   public function test_upload_image_to_mock_storage_fails(){
	$this->expectException(\ArgumentCountError::class);
        Storage::fake('image');
	$fname = \app\Http\Controllers\PageGenerationController::StoreAdImage();
   }

   public function test_remove_image_from_storage(){
	   $fname = $this->test_upload_image_to_mock_storage();
	   Storage::assertExists($fname);

	   $re = \app\Http\Controllers\ConfidentialInfoController::RemoveAdImage($fname);
	   Storage::assertMissing($fname);
   }

   public function test_remove_missing_image_from_storage(){
	   $fname = $this->test_upload_image_to_mock_storage();
	   Storage::assertExists($fname);

	   \app\Http\Controllers\ConfidentialInfoController::RemoveAdImage($fname . "z");
	   Storage::assertExists($fname);
   }


   //json
   public function test_add_sample_json_data(){
	//redundant but easy    
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);

	$token = $response->getOriginalContent()['access_token'];
 	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->get('api/details');

        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg');
	$fname = \app\Http\Controllers\PageGenerationController::StoreAdImage($img);


	\app\Http\Controllers\ConfidentialInfoController::addUserJSON($fname, "https://test.com");
	$this->assertEquals([['uri'=>$fname, 'url'=>'https://test.com']], json_decode(Storage::disk('local')->get("hardtest.json"), true));
	return $fname;
   }


   public function test_get_user_json_data(){
      $fname = $this->test_add_sample_json_data();
      $info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON();
      $this->assertEquals([['uri'=>$fname, 'url'=>'https://test.com']], $info);
   }

   public function test_remove_user_json_data(){
      $fname = $this->test_add_sample_json_data();
      \app\Http\Controllers\ConfidentialInfoController::removeUserJSON("$fname", "https://test.com");
      $this->assertEquals(json_decode(Storage::disk('local')->get("hardtest.json"), true), []);
   }

//sql

   public function test_fk_user_sql_ad_relation_established(){
    	$this->assertTrue(empty(DB::select('select * from ads')));
    }
    
    public function test_add_user_sql_ad_data(){
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);


	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("abc/123", "http://test.com");
	    $this->assertDatabaseHas("ads", ['fk_name'=>'test', 'uri'=>'abc/123', 'url'=>'http://test.com']);
    }

    public function test_remove_user_sql_ad_data(){
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	    \App\Http\Controllers\ConfidentialInfoController::removeAdSQL("abc/123", "http://test.com");
	    $this->assertDatabaseMissing("ads", ['fk_name'=>'test', 'uri'=>'abc/123', 'url'=>'http://test.com']);
    }

    public function test_all_page_get_info(){
$_SERVER["HTTP_X_REAL_IP"] = "2";

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
	$img2 = UploadedFile::fake()->image('ad1.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];	

	$response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
	$img3 = UploadedFile::fake()->image('ad2.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname3 = $response->json()['fname'];	

	$res = \App\Http\Controllers\PageGenerationController::getLimitedEntries();
	    $this->assertEquals(json_decode('[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b"},{"fk_name":"test3","uri":"c","url":"c"}]', true)[2]['fk_name'],json_decode($res, true)[2]['fk_name']);
    }

    public function test_all_page_get_info_under_effects_of_ban_pooling_disabled(){
	$_SERVER["HTTP_X_REAL_IP"] = "0";

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	$b = new Bans(['fk_name'=>'test']);
	$b->save();

	$_SERVER["HTTP_X_REAL_IP"] = "1";

	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img2 = UploadedFile::fake()->image('ad1.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];	

	$b = new Bans(['fk_name'=>'test2']);
	$b->save();

		$_SERVER["HTTP_X_REAL_IP"] = "2";
	$response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img3 = UploadedFile::fake()->image('ad2.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname3 = $response->json()['fname'];	

	$b = new Bans(['fk_name'=>'test3']);
	$b->save();

	
	$_SERVER["HTTP_X_REAL_IP"] = "1";

	$res = \App\Http\Controllers\PageGenerationController::getLimitedEntries('test2', false);
	$test_json = '[{"fk_name":"test2","uri":"a","url":"a"}]';
        $this->assertEquals(json_decode($test_json, true)[0]['fk_name'],json_decode($res, true)[0]['fk_name']);

    }

    public function test_all_page_get_info_IP_Connection_of_ban_pooling_disabled(){
	$_SERVER["HTTP_X_REAL_IP"] = "0";

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	$b = new Bans(['fk_name'=>'test']);
	$b->save();


	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img2 = UploadedFile::fake()->image('ad1.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];	

		$_SERVER["HTTP_X_REAL_IP"] = "2";
	$response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img3 = UploadedFile::fake()->image('ad2.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname3 = $response->json()['fname'];	

	$b = new Bans(['fk_name'=>'test3']);
	$b->save();

	$res = \App\Http\Controllers\PageGenerationController::getLimitedEntries('test', false);

	$test_json = '[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"a","url":"a"}]';
	$this->assertEquals(json_decode($test_json, true)[0]['fk_name'],json_decode($res, true)[0]['fk_name']);
	$this->assertEquals(json_decode($test_json, true)[1]['fk_name'],json_decode($res, true)[1]['fk_name']);


     }


    public function test_all_page_get_info_under_effects_of_ban_pooling_enabled(){
	    $_SERVER["HTTP_X_REAL_IP"] = "1";

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $t1 = $response->getOriginalContent()['access_token'];

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	$_SERVER["HTTP_X_REAL_IP"] = "2";
	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img2 = UploadedFile::fake()->image('ad1.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];

	$_SERVER["HTTP_X_REAL_IP"] = "3";
	$response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	$img3 = UploadedFile::fake()->image('ad2.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname3 = $response->json()['fname'];

	$b = new Bans(['fk_name'=>'test']);
	$b->save();
	$b = new Bans(['fk_name'=>'test2']);
	$b->save();
	$b = new Bans(['fk_name'=>'test3']);
	$b->save();
	
	$_COOKIE['freeadstoken'] = $t1;
	$res = \App\Http\Controllers\PageGenerationController::getLimitedEntries('test', true);
	$test_json = '[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b"},{"fk_name":"test3","uri":"c","url":"c"}]';
	$this->assertEquals(json_decode($test_json, true)[2]['fk_name'],json_decode($res, true)[2]['fk_name']);
	$this->assertEquals(json_decode($test_json, true)[1]['fk_name'],json_decode($res, true)[1]['fk_name']);
        $this->assertEquals(json_decode($test_json, true)[0]['fk_name'],json_decode($res, true)[0]['fk_name']);
     }


    
    public function test_random_sql_entry(){
         Storage::fake('public/image');
$_SERVER["HTTP_X_REAL_IP"] = 1;
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://a.com"]);
	 
$_SERVER["HTTP_X_REAL_IP"] = 2;
         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://b.com"]);

	    $a = 1;
	    $b = 1;	    
	for($i = 0 ; $i < 500 ; $i++){
		\App\Http\Controllers\PageGenerationController::GetRandomAdEntry()->url == "https://a.com" ? $a++ : $b++;
	}	
	    echo "$a $b";
	$this->assertEquals($a / $b > 0.75, $a / $b < 1.25);
    }

    public function test_IP_associated_with_image_sql(){
	$_SERVER["HTTP_X_REAL_IP"] = "1";
	
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://a.com"]);
	$fname1 = $response->json()['fname'];	
	$this->assertDatabaseHas('ads', ['ip'=>$_SERVER["HTTP_X_REAL_IP"]]);
    }

// ad page
    
    public function test_distributed_ad_page_reachable(){
	    $_SERVER["HTTP_X_REAL_IP"] = "1";
	    $re = $this->call('GET', 'banner');
	    $re->assertStatus(200);
    }

    public function test_if_in_ban_list(){
    	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $t1 = $response->getOriginalContent()['access_token'];

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	
	$b = new Bans(['fk_name'=>'test']);
	$b->save();
	
	$this->assertTrue(\app\Http\Controllers\PageGenerationController::checkBanned('test'));
    }
	
    public function test_IP_does_not_fail_on_no_HTTP_X_REAL_pagegen(){
	unset($_SERVER['HTTP_X_REAL_IP']);
    	$this->assertTrue(\app\Http\Controllers\PageGenerationController::getBestIPSource() == "127.0.0.1");
    }
    public function test_IP_does_not_fail_on_no_HTTP_X_REAL_confidential(){
	unset($_SERVER['HTTP_X_REAL_IP']);
    	$this->assertTrue(\app\Http\Controllers\ConfidentialInfoController::getBestIPSource() == "127.0.0.1");
    }

}
