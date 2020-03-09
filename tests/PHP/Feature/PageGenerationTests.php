<?php

namespace Tests\Feature;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

use App\Bans;

class PageGenerationTests extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

          
    public function test_new_ad_insertion(){

	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);	
	$response->assertStatus(200)->assertJson(['log'=>'Ad Created']);

	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
	$this->assertEquals('https://test.com', $info[0]['url']);
	$this->assertDatabaseHas("ads", ['fk_name'=>'test', 'url'=>'https://test.com']);
    }          
    public function test_ad_page_generation(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");


	$response = $this->call("GET", 'banner');
	var_dump($response->content());
	$response->assertViewHasAll(['name'=>'test', 'uri'=>str_replace('public','storage',$fname), 'url'=>'https://test.com']);

    }
    public function test_user_data_retrieval(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];	
	$this->assertDatabaseHas('ads', ['fk_name'=>'test', 'uri'=>$fname, 'url'=>'https://test.com']);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->get('api/details');
	$response->assertStatus(200);
	$this->assertEquals('{"name":"test","mod":false,"ads":[{"uri":"'. str_replace("/","\\/", $fname1) .'","url":"https:\/\/test.com"},{"uri":"' . str_replace("/","\\/", $fname1) . '","url":"https:\/\/test.com"}]}', $response->getContent());
    }

    public function test_user_ad_removal(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

		$this->assertDatabaseHas('ads', ['fk_name'=>'test', 'uri'=>$fname, 'url'=>'https://test.com']);

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/removal', ['uri'=>$fname, 'url'=>"https://test.com"]);
	$response->assertStatus(200)->assertJson(['log' => 'Ad Removed']);

	$this->assertTrue(empty(DB::select('select * from ads')), 'DB Empty Check');
	$this->assertEquals(json_decode(Storage::disk('local')->get("test.json"), true), []);
	Storage::disk('local')->assertMissing($fname);

    }

    public function test_user_cant_remove_ad(){
	    // try and remove this
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

	// other user
	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

	// tries to remove other's
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/removal', ['uri'=>$fname, 'url'=>"https://test.com"]);
	$response->assertStatus(401)->assertJson(['warn' => 'This banner isn\'t owned']);
	
	// there's still things in there
	$this->assertFalse(empty(DB::select('select * from ads')), 'DB Empty Check');
	$this->assertNotEquals(json_decode(Storage::disk('local')->get("test.json"), true), []);
	Storage::disk('local')->assertExists($fname);
    }


	public function test_image_is_owned_passes(){
		\App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
		\App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
		$this->assertTrue(\App\Http\Controllers\PageGenerationController::affirmImageIsOwned("test", "a"));
	}

    	public function test_image_is_owned_fails(){
		\App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
		\App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
		$this->assertFalse(\App\Http\Controllers\PageGenerationController::affirmImageIsOwned("testb", "a"));

	}

    public function test_banned_user_does_not_show(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    $ban = new Bans(['fk_name'=>'test2']);
	    $ban->save();
	    $a = 0;
	    $b = 0;	    
	for($i = 0 ; $i < 100 ; $i++){
		\App\Http\Controllers\PageGenerationController::GetRandomAdEntry()->uri == "a" ? $a++ : $b++;
	}	
	    echo "$a $b";
	$this->assertEquals($b, 0);
    }

	public function test_all_page_get_info(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test3", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test3", "c", "c");
	    $res = \App\Http\Controllers\PageGenerationController::getLimitedInfo();

	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test2","uri":"b","url":"b","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test","uri":"a","url":"a","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"}]', true)[2]['fk_name'], 
		    json_decode($res, true)[2]['fk_name']);
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
	    $res = \App\Http\Controllers\PageGenerationController::getLimitedInfo();

	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test","uri":"b","url":"b","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"}]', true)[1]['fk_name'], 
		    json_decode($res, true)[1]['fk_name']);
	}


}
