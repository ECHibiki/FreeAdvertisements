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
	   $response = $this->withHeaders(['Accept' => 'application/json'])->get("api/all", []);
	   $response->assertStatus(200);
   }
//image

   public function test_upload_image_to_mock_storage(){
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$fname = \app\Http\Controllers\PageGenerationController::StoreAdImage($img);
	Storage::assertExists($fname);
	return $fname;
   }
   public function test_upload_image_to_mock_storage_fails(){
	$this->expectException(\ArgumentCountError::class);
        Storage::fake('image');
	$fname = \app\Http\Controllers\PageGenerationController::StoreAdImage();
	Storage::assertExists($fname);
	return $fname;
   }

   public function test_remove_image_from_storage(){
	   $fname = $this->test_upload_image_to_mock_storage();
	   Storage::assertExists($fname);

	   \app\Http\Controllers\PageGenerationController::RemoveAdImage($fname);
	   Storage::assertMissing($fname);
   }

      public function test_remove_missing_image_from_storage(){
	   $fname = $this->test_upload_image_to_mock_storage();
	   Storage::assertExists($fname);

	   \app\Http\Controllers\PageGenerationController::RemoveAdImage($fname . "z");
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


	\app\Http\Controllers\ConfidentialInfoController::addUserJSON("hardtest", $fname, "https://test.com");
	$this->assertEquals([['uri'=>$fname, 'url'=>'https://test.com']], json_decode(Storage::disk('local')->get("hardtest.json"), true));
	return $fname;
   }


   public function test_get_user_json_data(){
      $fname = $this->test_add_sample_json_data();
      $info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("hardtest");
      $this->assertEquals([['uri'=>$fname, 'url'=>'https://test.com']], $info);
   }

   public function test_remove_user_json_data(){
      $fname = $this->test_add_sample_json_data();
      \app\Http\Controllers\ConfidentialInfoController::removeUserJSON("hardtest", "$fname", "https://test.com");
      $this->assertEquals(json_decode(Storage::disk('local')->get("hardtest.json"), true), []);

   }

//sql

   public function test_fk_user_sql_ad_relation_established(){
    	$this->assertTrue(empty(DB::select('select * from ads')));
    }
    
    public function test_add_user_sql_ad_data(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "abc/123", "http://test.com");
	    $this->assertDatabaseHas("ads", ['fk_name'=>'test', 'uri'=>'abc/123', 'url'=>'http://test.com']);
    }

    public function test_remove_user_sql_ad_data(){
	    $this->test_add_user_sql_ad_data();
	    \App\Http\Controllers\ConfidentialInfoController::removeAdSQL("test", "abc/123", "http://test.com");
	    $this->assertDatabaseMissing("ads", ['fk_name'=>'test', 'uri'=>'abc/123', 'url'=>'http://test.com']);
    }

    public function test_all_page_get_info(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test3", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test3", "c", "c");
	    $res = \App\Http\Controllers\PageGenerationController::getLimitedEntries();
	    $this->assertEquals(json_decode('[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b"},{"fk_name":"test3","uri":"c","url":"c"}]', true)[2]['fk_name'],json_decode($res, true)[2]['fk_name']);
    }

        public function test_all_page_get_info_under_effects_of_ban(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    $b = new Bans(['fk_name'=>'test2']);
	    $b->save();
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test3", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test3", "c", "c");
	    $res = \App\Http\Controllers\PageGenerationController::getLimitedEntries();
	    $this->assertEquals(json_decode('[{"fk_name":"test","uri":"b","url":"b"},{"fk_name":"test3","uri":"c","url":"c"}]', true)[1]['fk_name'], json_decode($res, true)[1]['fk_name']);
	}


    
    public function test_random_sql_entry(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "b", "b");
	    $a = 1;
	    $b = 1;	    
	for($i = 0 ; $i < 500 ; $i++){
		\App\Http\Controllers\PageGenerationController::GetRandomAdEntry()->uri == "a" ? $a++ : $b++;
	}	
	    echo "$a $b";
	$this->assertEquals($a / $b > 0.75, $a / $b < 1.25);
    }



// ad page
    
    public function test_distributed_ad_page_reachable(){
	    $re = $this->call('GET', 'banner');
	    $re->assertStatus(200);
    }
}
