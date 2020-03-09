<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Auth;

use App\Bans;
class ModTests extends TestCase
{
	use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_a_mod_login_fails(){
	//redundant but easy    
	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);
        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);

	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->get('api/details');  
	$this->assertEquals(json_decode($response->getContent(), true)['mod'], false);

    }

    public function test_a_mod_login_succeeds(){
	//redundant but easy    
	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	\App\Http\Controllers\ModeratorActivityController::createMod("hardtest");
	$this->assertDatabaseHas('mods', ['fk_name'=>'hardtest']);


	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);

        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);
	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->get('api/details');  
	$this->assertEquals(json_decode($response->getContent(), true)['mod'], true);

    }

	// test retrieval of a complete list for rout mod
	// test get all entries rotated
    public function test_get_complete_reversed_list_as_mod(){
    	//redundant but easy    
	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	\App\Http\Controllers\ModeratorActivityController::createMod("hardtest");
	$this->assertDatabaseHas('mods', ['fk_name'=>'hardtest']);


	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);

        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);
	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));


	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");
	    $b = new Bans(['fk_name'=>'test']);
	    $b->save();
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test2", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test2", "b", "b");
	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test3", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test3", "c", "c");

	    $res = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->json('get','api/mod/all'); 

	    $this->assertEquals(json_decode('[{"fk_name":"test3","uri":"c","url":"c","updated_at":"2020-03-09 00:08:27","created_at":"2020-03-09 00:08:27"},{"fk_name":"test2","uri":"b","url":"b","updated_at":"2020-03-09 00:08:27","created_at":"2020-03-09 00:08:27"},{"fk_name":"test","uri":"a","url":"a","updated_at":"2020-03-09 00:08:27","created_at":"2020-03-09 00:08:27"}]', true)[0]['fk_name'],	  json_decode($res->getContent(), true)[0]['fk_name']);
	}

    	// test the action route of banning user
	public function test_banning_action(){
		    	//redundant but easy    
	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	\App\Http\Controllers\ModeratorActivityController::createMod("hardtest");
	$this->assertDatabaseHas('mods', ['fk_name'=>'hardtest']);


	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);

        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);
	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));

	    \App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    \App\Http\Controllers\ConfidentialInfoController::addAdSQL("test", "a", "a");

	    $res = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->json('post','api/mod/ban', ['target'=>'test', 'hard'=>1]); 
		$in = \App\Http\Controllers\ModeratorActivityController::GetBanInfo("test");
		$this->assertEquals($in->hardban, 1);

	}
	// test route of purge
	public function test_individual_remove_action(){
				    	//redundant but easy    
		Storage::fake('local');

		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$response->assertStatus(200);
	$token = $response->getOriginalContent()['access_token'];
        $img = UploadedFile::fake()->image('ad.jpg',500,90);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	

	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");


	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass','pass_confirmation'=>'hardpass']);
	$response->assertStatus(200);

		\App\Http\Controllers\ModeratorActivityController::createMod("hardtest");
		$this->assertDatabaseHas('mods', ['fk_name'=>'hardtest']);

		$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);
		$response
			->assertStatus(200)
			->assertJson(['access_token'=>true]);
		$token = $response->getOriginalContent()['access_token'];
		$this->assertFalse($token == '' || is_null($token));

		$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

		$res = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->json('post','api/mod/individual', ['name'=>'test', 'uri'=>$fname, 'url'=>'https://test.com']); 

		$res->assertStatus(200);

		Storage::disk('local')->assertMissing($fname);
		$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
		$this->assertEquals($info, []);
		$this->assertDatabaseMissing('ads',['uri'=>$fname]);


	}
    
	// test route of prune
	
}
