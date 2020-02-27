<?php

namespace Tests\Feature;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;


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

	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',300,100);

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);	
	$response->assertStatus(200)->assertJson(['log'=>'Ad Created']);

	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");
	$this->assertEquals('https://test.com', $info[0]['url']);
	$this->assertDatabaseHas("ads", ['fk_name'=>'test', 'url'=>'https://test.com']);
    }          
    public function test_ad_page_generation(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('image');
        $img = UploadedFile::fake()->image('ad.jpg',300,100);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");


	$response = $this->call("GET", 'banner');
	var_dump($response->content());
	$response->assertViewHasAll(['name'=>'test', 'uri'=>str_replace('public','storage',$fname), 'url'=>'https://test.com']);

    }
    public function test_user_data_retrieval(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',300,100);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");


	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->get('api/details');
	    $response->assertStatus(200)->assertJsonStructure([
		    'name',
		    'ads'=>[
			       [
				'uri',
				'url'
				]
		    ]
	    ]);
    }

    public function test_user_ad_removal(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
	$token = $response->getOriginalContent()['access_token'];
        Storage::fake('public/image');
        $img = UploadedFile::fake()->image('ad.jpg',300,100);
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
	$fname = $response->json()['fname'];	
	$info = \app\Http\Controllers\ConfidentialInfoController::getUserJSON("test");

	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/removal', ['uri'=>$fname, 'url'=>"https://test.com"]);
	$response->assertStatus(200)->assertJson(['log' => 'Ad Removed']);

	$this->assertTrue(empty(DB::select('select * from ads')), 'DB Empty Check');
	$this->assertEquals(json_decode(Storage::disk('local')->get("test.json"), true), []);
	Storage::disk('local')->assertMissing($fname);

    }

}
