<?php

namespace Tests\Unit;

require "app/Http/Controllers/PageGenerationController.php";
require "app/Http/Controllers/UserCreationController.php";
require "app/Http/Controllers/UserSignInController.php";
require "app/Http/Controllers/ModeratorActivityController.php";


use Tests\TestCase;
use App\User;
use App\Bans;
use App\Mods;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Auth;
class DeveloperModTests extends TestCase
{

	use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

	public function test_a_mod_is_created(){
		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
		\App\Http\Controllers\ModeratorActivityController::createMod("test");
		$this->assertDatabaseHas('mods', ['fk_name'=>'test']);
	}
	public function test_a_mod_creation_fails(){
		$this->expectException(\Exception::class);
		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
		\App\Http\Controllers\ModeratorActivityController::createMod("test");
		\App\Http\Controllers\ModeratorActivityController::createMod("test");

	}

	// test get all entries
	//
	public function test_get_complete_list_as_mod(){
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    $b = new Bans(['fk_name'=>'test']);
	    $b->save();
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test3", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test3", "c", "c");

	    $res = \App\Http\Controllers\ModeratorActivityController::GetAllEntries();
		
	    $this->assertEquals(json_decode('[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b"},{"fk_name":"test3","uri":"c","url":"c"}]', true)[0]['fk_name'],json_decode($res, true)[0]['fk_name']);
	}
	//
	// test and make new check if already banned
	public function test_get_user_ban_info(){
		$u = new User(['name'=>'test', 'pass'=>'123']);
		$u->save();
		$b = new Bans(['fk_name'=>'test', 'hard'=>1]);
		$b->save();
		$in = \App\Http\Controllers\ModeratorActivityController::GetBanInfo("test");
		$this->assertEquals($in->hardban, 1);
	}


	// test db ad removal from mod
	public function test_db_removal_on_individual(){
			Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");


		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromDB($fname);

		$this->assertDatabaseMissing('ads',['uri'=>$fname]);

	}
	//
	// test json ad removal from mod
	public function test_json_removal_on_individual(){
			Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	


		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromJSON("test",$fname, 'https://test.com');
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

		$this->assertEquals($info, []);

	}

	// test file removal
		public function test_image_removal_on_individual(){
			Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	


		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromImages($fname);
		Storage::fake('local')->assertMissing($fname);

	}

	// test complete db removal from mod
	//
	// test complete json removal from mod
	//
	// test file removal
}
