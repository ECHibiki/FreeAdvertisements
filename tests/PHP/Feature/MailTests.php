<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use App\Mail\BannerNotification;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;

use Auth;

use App\Ban;

class MailTests extends TestCase
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

    	// test banner creation email succeeds for all CC when called with api key
	public function test_banner_creation_calls_send_message_to_mod_emails(){
		Mail::fake();
		Storage::fake('local');
		Storage::fake('public/image');
$_SERVER['HTTP_X_REAL_IP'] = 1;

		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$response->assertStatus(200);
		$token = $response->getOriginalContent()['access_token'];
        	$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
		$response->assertStatus(200);


		Mail::assertSent(BannerNotification::class, 1);

	}

	// method doesn't fire during cooldown for banner creation
	public function test_email_caller_does_not_fire_on_cooldown(){
		Mail::fake();
		Storage::fake('local');
		Storage::fake('public/image');
$_SERVER['HTTP_X_REAL_IP'] = 1;

		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$response->assertStatus(200);
		$token = $response->getOriginalContent()['access_token'];
        	$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);

sleep(1);

		$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
		$response->assertStatus(200);
		$token = $response->getOriginalContent()['access_token'];

		$response->assertStatus(200);
        	$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);

		$response->assertStatus(200);

		Mail::assertSent(BannerNotification::class, 1);

	}

	// method fires after cooldown for banner creation
	public function test_email_caller_fires_after_cooldown(){
		Mail::fake();
		Storage::fake('local');
		Storage::fake('public/image');
$_SERVER['HTTP_X_REAL_IP'] = 1;
		$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);

		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
		$response->assertStatus(200);
		$token = $response->getOriginalContent()['access_token'];
        	$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);
var_dump($response->json());
		sleep(6);

		$response = $this->call('POST', 'api/create', ['name'=>'test2', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
		$response->assertStatus(200);
		$response = $this->call('POST', 'api/login', ['name'=>'test2', 'pass'=>'hardpass']);
		$response->assertStatus(200);
		$token = $response->getOriginalContent()['access_token'];
        	$img = UploadedFile::fake()->image('ad.jpg',500,90);
		$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com"]);


		Mail::assertSent(BannerNotification::class, 2);
	}
}
