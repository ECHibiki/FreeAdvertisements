<?php

namespace Tests\Unit;

require "app/Http/Controllers/PageGenerationController.php";
require "app/Http/Controllers/UserCreationController.php";
require "app/Http/Controllers/UserSignInController.php";
require "app/Http/Controllers/ModeratorActivityController.php";


use Tests\TestCase;
use App\User;
use App\Ban;
use App\Mod;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Auth;
use App\Mail\BannerNotification;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
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
				$_SERVER['HTTP_X_REAL_IP'] = 1;

         $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://a.com"]);
sleep(env('COOLDOWN',60)+1);
         $response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
         $response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
         Storage::fake('public/image');
         $img = UploadedFile::fake()->image('ad.jpg',500,90);
	 $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $response->getOriginalContent()['access_token'], 'enctype'=>'multipart/form-data'])->post('api/details',['image'=>$img, 'url'=>"https://b.com"]);
	 $ban = new Ban(['fk_name'=>'test2', 'hardban'=>0]);
	 $ban->save();
sleep(env('COOLDOWN',60)+1+0.5);
	    $res = \App\Http\Controllers\ModeratorActivityController::GetAllEntries();
	 $this->assertEquals(json_decode('[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b", "hardban":0}]', true)[0]['fk_name'],json_decode($res, true)[0]['fk_name']);
	 $this->assertEquals(json_decode('[{"fk_name":"test","uri":"a","url":"a"},{"fk_name":"test2","uri":"b","url":"b", "hardban":0}]', true)[1]['hardban'],json_decode($res, true)[1]['hardban']);

	}
	//
	// test and make new check if already banned
	public function test_get_user_ban_info(){
		$u = new User(['name'=>'test', 'pass'=>'123']);
		$u->save();
		$b = new Ban(['fk_name'=>'test', 'hard'=>1]);
		$b->save();
		$in = \App\Http\Controllers\ModeratorActivityController::GetBanInfo("test");
		$this->assertEquals($in->hardban, 1);
	}


	// test db ad removal from mod
	public function test_db_removal_on_individual(){
				$_SERVER['HTTP_X_REAL_IP'] = 1;

			Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
sleep(env('COOLDOWN',60)+1);

		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromDB($fname);

		$this->assertDatabaseMissing('ads',['uri'=>$fname]);

	}
	//
	// test json ad removal from mod
	public function test_json_removal_on_individual_generic(){
		$_SERVER['HTTP_X_REAL_IP'] = 1;

		Storage::fake('local');
		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$token = $response->getOriginalContent()['access_token'];
		Storage::fake('image');

		$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
		$fname3 = $response->json()['fname'];
		sleep(env('COOLDOWN',60)+1);
		$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
		$fname = $response->json()['fname'];
		sleep(env('COOLDOWN',60)+1);
		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromJSON("test",$fname, 'https://test.com');
		$info = \app\Http\Controllers\ModeratorActivityController::getSelectJSON("test");

		$this->assertEquals($info, '[{"uri":"'. str_replace("/", "\/", $fname3) .'","url":"https:\/\/test.com","size":"wide","clicks":"0"}]');
	}

	// test file removal
	public function test_image_removal_on_individual(){
				$_SERVER['HTTP_X_REAL_IP'] = 1;

			Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
	Storage::fake('public/image');

	$img1 = UploadedFile::fake()->image('ad.jpg',500,90);
	$response1 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img1, 'url'=>"https://test.com"]);
	$fname1 = $response1->json()['fname'];
sleep(env('COOLDOWN',60)+1);
	$img2 = UploadedFile::fake()->image('ad2.jpg',500,90);
	$response2 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
	$fname2 = $response2->json()['fname'];
sleep(env('COOLDOWN',60)+1);
		\App\Http\Controllers\ModeratorActivityController::removeIndividualBannerFromImages($fname1);
		Storage::disk('local')->assertMissing($fname1);
		Storage::disk('local')->assertExists($fname2);


     }

	// test complete db removal from mod
	public function test_complete_db_removal_from_mod(){
				$_SERVER['HTTP_X_REAL_IP'] = 1;

				// to be destroyed
     		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$token = $response->getOriginalContent()['access_token'];
		Storage::fake('public/image');

		$img1 = UploadedFile::fake()->image('ad.jpg',500,90);
		$response1 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img1, 'url'=>"https://test.com"]);
		$fname1 = $response1->json()['fname'];
		$response1->assertStatus(200);
		sleep(env('COOLDOWN',60)+1);
		$img2 = UploadedFile::fake()->image('ad2.jpg',500,90);
		$response2 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
		$fname2 = $response2->json()['fname'];
sleep(env('COOLDOWN',60)+1);
		// saftey test

     		$response = $this->call('POST', 'api/create', ['name'=>'test1', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response = $this->call('POST', 'api/login', ['name'=>'test1', 'pass'=>'hardpass']);
		$tokenf = $response->getOriginalContent()['access_token'];

		$imgf = UploadedFile::fake()->image('ad.jpg',500,90);
		$responsef = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $tokenf, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$imgf, 'url'=>"https://test.com"]);
		$fnamef = $responsef->json()['fname'];
		$responsef->assertStatus(200);
sleep(env('COOLDOWN',60)+1);

		\App\Http\Controllers\ModeratorActivityController::removeUserFromDatabase("test");

		$this->assertDatabaseMissing('ads', ['fk_name'=>'test', 'uri'=>$fname1, 'url'=>'https://test.com']);
		$this->assertDatabaseMissing('ads', ['fk_name'=>'test', 'uri'=>$fname2, 'url'=>'https://test.com']);
		$this->assertDatabaseHas('ads', ['fk_name'=>'test1']);

     	}
	// test complete json removal from mod
	public function test_complete_json_removal(){
				$_SERVER['HTTP_X_REAL_IP'] = 1;

	     	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$token = $response->getOriginalContent()['access_token'];
		Storage::fake('public/image');

		$img1 = UploadedFile::fake()->image('ad.jpg',500,90);
		$response1 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img1, 'url'=>"https://test.com"]);
		$fname1 = $response1->json()['fname'];
sleep(env('COOLDOWN',60)+1);
		$img2 = UploadedFile::fake()->image('ad2.jpg',500,90);
		$response2 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
		$fname2 = $response2->json()['fname'];
sleep(env('COOLDOWN',60)+1);
		\App\Http\Controllers\ModeratorActivityController::truncateUserJSON("test");

		$this->assertEquals(Storage::disk('local')->get('test.json'),'[]');
	}

	// test images removed
	public function test_complete_image_removal(){
		$_SERVER['HTTP_X_REAL_IP'] = 1;
	     	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$token = $response->getOriginalContent()['access_token'];
		Storage::fake('public/image');

		$img1 = UploadedFile::fake()->image('ad.jpg',500,90);
		$response1 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img1, 'url'=>"https://test.com"]);
		$fname1 = $response1->json()['fname'];
		$response1->assertStatus(200);
		sleep(env('COOLDOWN',60)+1);
		$img2 = UploadedFile::fake()->image('ad2.jpg',500,90);
		$response2 = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img2, 'url'=>"https://test.com"]);
		$fname2 = $response2->json()['fname'];
sleep(env('COOLDOWN',60)+1);
		\App\Http\Controllers\ModeratorActivityController::removeAllUserImages("test");

		Storage::fake('public/image')->assertMissing($fname1);
		Storage::fake('public/image')->assertMissing($fname2);

	}



}
