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

class RedirectTests extends TestCase
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

    public function test_Banner_Returns_Click_And_URL(){
      Storage::fake('local');
      $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
      $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
      $token = $response->getOriginalContent()['access_token'];
      Storage::fake('image');
      $img = UploadedFile::fake()->image('ad.jpg',env('MIX_IMAGE_DIMENSIONS_W',500),env('MIX_IMAGE_DIMENSIONS_H',90));
      $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com", 'size'=>'wide']);

      $fname = $response->json()['fname'];
      $fname = substr($fname, strrpos($fname, '/') + 1);

      $response = $this->call("GET", 'req', ['s'=> 'https://test.com', 'f'=>$fname]);
      $response->assertStatus(302);
      $response->assertRedirect('https://test.com');

      $this->assertDatabaseHas('ads', ['clicks'=>1]);
      $this->assertDatabaseMissing('ads', ['clicks'=>0]);
    }

    public function test_Banner_Fails(){
      Storage::fake('local');
      $response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hardpass', 'pass_confirmation'=>'hardpass']);
      $response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hardpass']);
      $token = $response->getOriginalContent()['access_token'];
      Storage::fake('image');
      $img = UploadedFile::fake()->image('ad.jpg',env('MIX_IMAGE_DIMENSIONS_W',500),env('MIX_IMAGE_DIMENSIONS_H',90));
      $response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token, 'enctype'=>'multipart/form-data'])->post('api/details', ['image'=>$img, 'url'=>"https://test.com", 'size'=>'wide']);

      $fname = $response->json()['fname'];

      $response = $this->call("GET", 'req', ['s'=> '', 'f'=>'']);
      $response->assertStatus(404);
      $this->assertDatabaseHas('ads', ['clicks'=>0]);
      $this->assertDatabaseMissing('ads', ['clicks'=>1]);
    }
}
