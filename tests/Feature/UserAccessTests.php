<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Auth;

class UserAccessTests extends TestCase
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

    // user creation tests
  
    public function test_a_user_is_sucessfuly_created(){
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hashedtestpass']);
	
	$response
		->assertStatus(200)
		->assertJson([
			'created' => '1'
            ]);
	
	//check db for user
	$this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedtestpass']) != false);
	Storage::disk('local')->assertExists("test.json");
	$this->assertTrue(Storage::get('test.json') == "[]", "json malformated");
    }   

    public function test_a_duplicate_user_creation_fails(){
	Storage::fake('local');
	$this->test_a_user_is_sucessfuly_created();

    	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hashedtestpass2']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "0",
            ]);

	//check db for user
	$this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedtestpass2']) == false);
    }

    public function test_password_field_does_not_exist_and_user_is_not_created(){   
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "-2",
            ]);

	//check db for user
	$this->assertDatabaseMissing('users', [
            'name'=>'test', 'password'=>''
	]);
	Storage::disk('local')->assertMissing("test.json");
    }
    public function test_name_field_does_not_exist_and_user_is_not_created(){  
	$response = $this->call('POST', 'api/create', ['name'=>'', 'pass'=>'hashedtestpass']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "-1",
            ]);

	//check db for user
	$this->assertTrue(Auth::attempt(['name'=>'', 'password'=>'hashedtestpass']) == false);
    }

// login tests

     public function test_an_existing_user_logs_in(){
	//redundant but easy    
	Storage::fake('local');

	$response = $this->call('POST', 'api/create', ['name'=>'hardtest', 'pass'=>'hardpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'hardtest', 'pass'=>'hardpass']);
        $response
		->assertStatus(200)
		->assertJson(['access_token'=>true]);

	$token = $response->getOriginalContent()['access_token'];
	$this->assertFalse($token == '' || is_null($token));
	//unlink("resources/user-json/test.json");

    	return $token;
	//evaluate token as needed
    }

    public function test_a_bad_pass_for_existing_user_tries_to_log_in(){
	    //redundant but easy     
	Storage::fake('local');
	$response = $this->call('POST', 'api/create', ['name'=>'test', 'pass'=>'hashedtestpass']);
	$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hash']);
        $response
		->assertStatus(401)
		->assertJson(['log'=>'-1'])
		->assertJsonMissing(['access_token']); //login error

    }

    
// jwt auth tests

    public function test_a_legitimate_token_is_used_to_access_private_page(){
	//redundant but easy    
	$token = $this->test_an_existing_user_logs_in();
    	//evaluate token as needed
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer ' . $token])->get('api/details');
	$response->assertStatus(200);
    }

    public function test_a_token_is_expired_when_used_on_private_page(){
        //redundant but easy    
        //$token = $this->test_an_existing_user_logs_in();
    	//evaluate token as needed
	$response = $this->withHeaders(['Accept' => 'application/json', 'Authorization'=>'bearer agarbage'])->get('api/details');
	$response->assertStatus(401);

    }

}
