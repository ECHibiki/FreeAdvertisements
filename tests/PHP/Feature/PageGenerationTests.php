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

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://www.test.com/?asd=123"]);	
	$response->assertStatus(200)->assertJson(['log'=>'Ad Created']);

	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
	$this->assertEquals('https://www.test.com/?asd=123', $info[0]['url']);
	$this->assertDatabaseHas("ads", ['fk_name'=>'test', 'url'=>'https://www.test.com/?asd=123']);
	Storage::disk('local')->assertExists($info[0]['uri']);  


	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com/"]);	
	$response->assertStatus(200)->assertJson(['log'=>'Ad Created']);

	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
	$this->assertEquals('https://test.com/', $info[1]['url']);
	$this->assertDatabaseHas("ads", ['fk_name'=>'test', 'url'=>'https://test.com/']);
	Storage::disk('local')->assertExists($info[1]['uri']);  

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
	$response->assertViewHasAll(['name'=>'test', 'uri'=>str_replace('public','storage',$fname), 'url'=>'https://test.com']);

    }
    public function test_user_data_retrieval(){
	    Storage::fake('local');
	    Storage::fake('public/image');

	//other person
	$response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

        $img3 = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img3, 'url'=>"https://test.com"]);
	$fname3 = $response->json()['fname'];	


// retrieval
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];

        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

	$img2 = UploadedFile::fake()->image('ad1.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response->json()['fname'];	


	$this->assertDatabaseHas('ads', ['fk_name'=>'test', 'uri'=>$fname2, 'url'=>'https://test.com']);


	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->get('api/details');
	$response->assertStatus(200);


	$this->assertEquals('{"name":"test","mod":false,"ads":[{"uri":"'. str_replace("/","\\/", $fname2) .'","url":"https:\/\/test.com"},{"uri":"' . str_replace("/","\\/", $fname1) . '","url":"https:\/\/test.com"}]}', $response->getContent());
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
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	

	// other user
	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
	$token2 = $response->getOriginalContent()['access_token'];

	// tries to remove other's
	Storage::disk('local')->assertExists($fname);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token2, 'enctype'=>'multipart/form-data'])->post('api/removal', ['uri'=>$fname, 'url'=>"https://test.com"]);
	$response->assertStatus(401)->assertJson(['warn' => 'This banner isn\'t owned']);
	// there's still things in there
	$this->assertFalse(empty(DB::select('select * from ads')), 'DB Empty Check');
	$this->assertNotEquals(json_decode(Storage::disk('local')->get("test.json"), true), []);
	Storage::disk('local')->assertExists($fname);
    }


    public function test_image_is_owned_passes(){
	    Storage::fake('local');
	    Storage::fake('public/image');

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	

		$this->assertTrue(\App\Http\Controllers\ConfidentialInfoController::affirmImageIsOwned("$fname1"));
	}

    public function test_image_is_owned_fails(){
	    Storage::fake('local');
	    Storage::fake('public/image');

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname1 = $response->json()['fname'];	


	$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);


		$this->assertFalse(\App\Http\Controllers\ConfidentialInfoController::affirmImageIsOwned("fname1"));

	}

    public function test_banned_user_does_not_show(){
$_SERVER["HTTP_X_REAL_IP"] = 1;
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://a.com"]);
	 $ban = new Bans(['fk_name'=>'test']);
	 $ban->save();


$_SERVER["HTTP_X_REAL_IP"] = 2;
         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://b.com"]);

	    $a = 0;
	    $b = 0;	    
	for($i = 0 ; $i < 100 ; $i++){
		\App\Http\Controllers\PageGenerationController::GetRandomAdEntry()->url == "https://a.com" ? $a++ : $b++;
	}	
	    echo "$a $b";
	$this->assertEquals($a, 0);
    }

     public function test_banned_users_see_there_own(){
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://a.com"]);

         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://b.com"]);

	 $ban = new Bans(['fk_name'=>'test']);
	 $ban->save();
	 $ban = new Bans(['fk_name'=>'test2']);
	 $ban->save();

	    $a = 1;
	    $b = 1;	    
	for($i = 0 ; $i < 500 ; $i++){
		\App\Http\Controllers\PageGenerationController::GetRandomAdEntry()->url == "https://a.com" ? $a++ : $b++;
	}	
	    echo "$a $b";
	$this->assertTrue($b / $a >= 0.75 || $a / $b >= 0.75);
    }

	public function test_all_page_get_info(){
	//redundant but easy    
	Storage::fake('local');

         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);

         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);

         $response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	

	    $res = $this->withHeaders(['Accept' => 'application/json'])->json('get','api/all', ['env'=>'true'], ['freeadstoken'=>$response->getOriginalContent()['access_token']]); 

	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test2","uri":"b","url":"b","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test","uri":"a","url":"a","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"}]', true)[2]['fk_name'], 
		    json_decode($res->getOriginalContent(), true)[2]['fk_name']);
    }

      public function test_all_page_get_info_under_effects_of_ban_for_normal_user(){
	//redundant but easy    
	Storage::fake('local');
$_SERVER["HTTP_X_REAL_IP"] = 1;
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test']);
	    $b->save();
$_SERVER["HTTP_X_REAL_IP"] = 2;
         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test2']);
	    $b->save();
$_SERVER["HTTP_X_REAL_IP"] = 3;
         $response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test3']);
	    $b->save();
$_SERVER["HTTP_X_REAL_IP"] = 4;
	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);
        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);
	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));


	    $res = $this->withHeaders(['Accept' => 'application/json'])->json('get','api/all'); 
	    $this->assertEquals(json_decode($res->getContent(), true), []);

	}

     public function test_all_page_get_info_under_effects_of_ban_for_banned_user_pooling_enabled(){
	//redundant but easy    
	Storage::fake('local');

$_SERVER["HTTP_X_REAL_IP"] = 1;
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test']);
	    $b->save();
$_SERVER["HTTP_X_REAL_IP"] = 2;
         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test2']);
	    $b->save();
$_SERVER["HTTP_X_REAL_IP"] = 3;
         $response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	 $response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
	 $token =  $response->getOriginalContent()['access_token'];
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test3']);
	    $b->save();


	    $res = $this->withHeaders(['Accept' => 'application/json'])->json('get','api/all', ['env'=>'true'], ['freeadstoken'=>$token]); 

	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test2","uri":"b","url":"b","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test","uri":"a","url":"a","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"}]', true)[2]['fk_name'], 
		    json_decode($res->getContent(), true)[2]['fk_name']);

	}

     public function test_all_page_get_info_under_effects_of_ban_for_unbanned_users_with_same_ip(){
	//redundant but easy    
	Storage::fake('local');
 $_SERVER["HTTP_X_REAL_IP"] = 1;
         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test']);
	    $b->save();

         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test2']);
	    $b->save();

         $response = $this->call('POST', 'api/create', ['name'=>'test3', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test3', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://test.com"]);
	    $b = new Bans(['fk_name'=>'test3']);
	    $b->save();

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);
        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);
	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));


	    $res = $this->withHeaders(['Accept' => 'application/json'])->json('get','api/all',[], ['freeadstoken'=>$token]); 
	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test2","uri":"b","url":"b","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"},{"fk_name":"test","uri":"a","url":"a","updated_at":"2020-03-08 20:10:36","created_at":"2020-03-08 20:10:36"}]', true)[2]['fk_name'], 
		    json_decode($res->getContent(), true)[2]['fk_name']);

     }

    public function test_empty_banner_JSON_call_does_not_fail(){
    	$res = $this->json('get', 'api/banner');
	$res->assertStatus(200);
	$this->assertEquals('[{"url":"","uri":"","name":"asdf no ads"}]', $res->getContent());
    }

}
