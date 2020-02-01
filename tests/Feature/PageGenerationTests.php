<?php

namespace Tests\Feature;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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

    private function userGenerationProcedure(){
        //redundant but easy
	unlink('user/data/test.json');
    
        $response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
        $response
                ->assertStatus(200)
                ->assertJson([
                'created' => '1',
            ]);

        $this->assertFileExists(public_path('user/data/test.json'));
        $this->assertEquals(json_decode(file_get_contents(public_path('user/data/test.json')) == json_decode("{[]}")));


        $response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
                ->assertStatus(200)
                ->assertJsonCount(1, 'jwt');

        $token = $this->response->getOriginalContent()->getData();
        return $token;
        //evaluate token as needed

    }

    public function user_accesses_their_details_json_sucessfully(){
	$token = user_creates_a_new_advertisement_successfully();
	
	$response = $this->call('POST', 'user/ad-details.php', ['jwt'=>$token]);
        $response
                ->assertStatus(200)
		->assertJson([['url'=>'https://genericurl.com','img'=>'user/image/198231.jpg']]);
    
    }

    public function user_accesses_their_details_json_without_authentication(){
        $token = user_creates_a_new_advertisement_successfully();

        $response = $this->call('POST', 'user/ad-details.php', ['jwt'=>'garbage.token.data']);
        $response
                ->assertStatus(401)
                ->assertJson(['ret'=>'-1']);

    }

    public function user_creates_a_new_advertisement_successfully(){
        $token = userGenerationProcedure();
        
	Storage::fake('files');
        $file = UploadedFile::fake()->image('file.jpg');
        $response = $this->call('POST', 'actions/create-ad.php', ['jwt'=>$token, 'site'=>'https://genericurl.com',
		'avatar' => $file
	]);

        // Assert the file was stored...
        Storage::disk('files')->assertExists(hash("sha256", $file->hashName()));

        // Assert a file does not exist...
        Storage::disk('files')->assertMissing($file->hashName());

	//JSON data test
        $this->assertFileExists(public_path('user/data/test.json'));
        $this->assertEquals(json_decode(file_get_contents(public_path('user/data/test.json')) == 
		json_decode("{[['url':'https://genericurl.com','img'=>'user/image" . hash("sha256", $file->hashName()) . "']]}")));

	//database test
	$this->assertDatabaseHas('users', [
                'name'=>'test', 'img'=>hash("sha256", $file->hashName()), 'site'
       ]);


	return $token;  
    }

    public function user_creates_a_new_advertisement_lacking_auth(){
        $token = userGenerationProcedure();

        Storage::fake('files');
        $file = UploadedFile::fake()->image('file.jpg');
        $response = $this->call('POST', 'actions/create-ad.php', ['jwt'=>'garbage.data.given', 'site'=>'https://genericurl.com',
                'avatar' => $file
        ]);

        // Assert the file was stored...
        Storage::disk('files')->assertMissing(hash("sha256", $file->hashName()));

        //JSON data test
        $this->assertFileMissing(public_path('user/data/test.json'));

        //database test
        $this->assertDatabaseMissing('users', [
                'name'=>'test', 'img'=>hash("sha256", $file->hashName()), 'site'=>'https://genericurl.com'
       ]);


    }

    public function user_creates_a_new_advertisement_lacking_file(){
        $token = userGenerationProcedure();

        Storage::fake('files');
        $file = UploadedFile::fake()->image('file.jpg');
        $response = $this->call('POST', 'actions/create-ad.php', ['jwt'=>'garbage.data.given', 'site'=>'https://genericurl.com',
                'avatar' => ''
        ]);

        // Assert the file was stored...
        Storage::disk('files')->assertMissing(hash("sha256", $file->hashName()));

        //JSON data test
        $this->assertFileMissing(public_path('user/data/test.json'));

        //database test
        $this->assertDatabaseMissing('users', [
                'name'=>'test', 'img'=>hash("sha256", $file->hashName()), 'site'=>'https://genericurl.com'
       ]);

    }

    public function user_creates_a_new_advertisement_with_malformed_url(){
        $token = userGenerationProcedure();

        Storage::fake('files');
        $file = UploadedFile::fake()->image('file.jpg');
        $response = $this->call('POST', 'actions/create-ad.php', ['jwt'=>'garbage.data.given', 'site'=>'om',
                'avatar' => $file
        ]);

        // Assert the file was stored...
        Storage::disk('files')->assertMissing(hash("sha256", $file->hashName()));

        //JSON data test
        $this->assertFileMissing(public_path('user/data/test.json'));

        //database test
        $this->assertDatabaseMissing('users', [
                'name'=>'test', 'img'=>hash("sha256", $file->hashName()), 'site'=>'https://genericurl.com'
       ]);

    }

    public function a_random_advertisement_is_requested(){
	user_creates_a_new_advertisement_successfully();
	$response = $this->call('GET', 'generator.php');
	$response
		->assertStatus(200);
    }

}
