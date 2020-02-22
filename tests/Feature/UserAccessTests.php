<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;

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

    // user creation
    public function test_a_user_is_sucessfuly_created(){
	$response = $this->call('POST', 'create', ['name'=>'test', 'pass'=>'hashedtestpass']);
	
	$response
		->assertStatus(200)
		->assertJson([
			'created' => '1'
            ]);
	
	//check db for user
	$this->assertDatabaseHas('free_ad_users', [
        	'name'=>'test', 'pass'=>'hashedtestpass'
    	    ]);
	$this->assertTrue(file_exists("resources/user-json/test.json"), "no usr json");
	$this->assertTrue(file_get_contents('resources/user-json/test.json') == "[{}]", "json malformated");
	unlink("resources/user-json/test.json");

    }   

    public function test_a_duplicate_user_creation_fails(){
	$this->test_a_user_is_sucessfuly_created();

    	$response = $this->call('POST', 'create', ['name'=>'test', 'pass'=>'hashedtestpass2']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "0",
            ]);

	//check db for user
	$this->assertDatabaseMissing('free_ad_users', [
            'password'=>'hashedtestpass2', 'name'=>'test'
	]);
    }

    public function test_password_field_does_not_exist_and_user_is_not_created(){   
	$response = $this->call('POST', 'create', ['name'=>'test', 'password'=>'']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "-2",
            ]);

	//check db for user
	$this->assertDatabaseMissing('free_ad_users', [
            'name'=>'test', 'password'=>''
	]);
	$this->assertFalse(file_exists("resources/user-json/test.json"));
    }
    public function test_name_field_does_not_exist_and_user_is_not_created(){  
	$response = $this->call('POST', 'create', ['name'=>'', 'password'=>'hashedtestpass']);
	$response
		->assertStatus(401)
		->assertJson([
                'created' => "-1",
            ]);

	//check db for user
	$this->assertDatabaseMissing('free_ad_users', [
            'name' => '', 'password'=>'hashedtestpass'
	]);
    }


	// TODO: include jwt testing
    public function an_existing_user_logs_in(){
	//redundant but easy    
        unlink('user/data/test.json');

	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1'
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

    public function a_non_existing_user_tries_to_log_in(){
  	//redundant but easy    
	unlink('user/data/test.json');    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(401)
		->assertJson(['log'=>'-1']); //login error

    }

    public function an_existing_user_logs_out(){
	unlink('user/data/test.json');    
	//redundant but easy    
        $token = an_existing_user_logs_in();
    	//evaluate token as needed
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(200)
		->assertJson(['log'=>'1']); //success
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(401)
		->assertJson(['log'=>'-2']); //invalid token error
    }

    public function a_non_existing_user_logs_out(){
	unlink('user/data/test.json');    
	$token = "garbage~.^data.inzide";
    	//evaluate token as needed
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(400)
		->assertJson(['log'=>'-2']); //invalid token error

    }

    public function a_legitimate_token_is_used_to_access_private_page(){
	unlink('user/data/test.json');    
	//redundant but easy    
        $token = an_existing_user_logs_in();
    	//evaluate token as needed
        $response = $this->call('POST', 'user/user-data.php', ['jwt'=>$token]);
	$response
		->assertStatus(200);
    }

    public function a_token_is_expired_when_used_on_private_page(){
	unlink('user/data/test.json');
        //redundant but easy    
        $token = an_existing_user_logs_in();
    	//evaluate token as needed
        $response = $this->call('POST', 'user/user-data.php', ['jwt'=>$token]);
	$response
		->assertStatus(401)
		->assertJson(['access'=>'-1']);

    }

}
