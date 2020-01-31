<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAccessTests extends TestCase
{

    use RefershDatabase;

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

    //jwt token generation and user session
    public function a_user_is_sucessfuly_created(){
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);
	
	//check db for user
	$this->assertDatabaseHas('users', [
        	'email' => 'adtest10080@gmail.com', 'password'=>'hashedtestpass', 'name'=>'test'
    	    ]);
    }   

    public function a_duplicate_user_is_created(){
    	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(400)
		->assertJson([
                'created' => "0",
            ]);

	//check db for user
	$this->assertDatabaseMissing('users', [
            'email' => 'adtest10080@gmail.com', 'password'=>'hashedtestpass', 'name'=>'test'
	]);
    }

    public function email_field_does_not_exist_and_user_is_created(){
        $response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'']);
	$response
		->assertStatus(400)
		->assertJson([
                'created' => "-3",
            ]);

	//check db for user
	$this->assertDatabaseMissing('users', [
            'password'=>'hashedtestpass', 'name'=>'test'
	]);
    }

    public function email_field_does_not_exist_and_user_is_created(){
        $response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'', 'email'=>'']);
	$response
		->assertStatus(400)
		->assertJson([
                'created' => "-2",
            ]);

	//check db for user
	$this->assertDatabaseMissing('users', [
            'email' => 'adtest10080@gmail.com', 'name'=>'test'
	]);
    }
    public function name_field_does_not_exist_and_user_is_created(){
        $response = $this->call('POST', 'user/register.php', ['name'=>'', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(400)
		->assertJson([
                'created' => "-1",
            ]);

	//check db for user
	$this->assertDatabaseMissing('users', [
            'email' => 'adtest10080@gmail.com', 'password'=>'hashedtestpass'
	]);
    }


    public function an_existing_user_logs_in(){
	//redundant but easy    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(200)
		->assertJsonCount(1, 'jwt');

        $token = $this->response->getOriginalContent()->getData();
    	//evaluate token as needed
    }

    public function a_non_existing_user_tries_to_log_in(){
  	//redundant but easy    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(401)
		->assertJson('log'=>'-1'); //login error

    }

    public function an_existing_user_logs_out(){
        //redundant but easy    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(200)
		->assertJsonCount(1, 'jwt');

        $token = $this->response->getOriginalContent()->getData();
    	//evaluate token as needed
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(200)
		->assertJson('log'=>'1'); //success
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(401)
		->assertJson('log'=>'-2'); //invalid token error
    }

    public function a_non_existing_user_logs_out(){
        $token = "garbage~.^data.inzide";
    	//evaluate token as needed
	$response = $this->call('POST', 'user/logout.php', ['jwt'=>$token]);
        $response
		->assertStatus(400)
		->assertJson('log'=>'-2'); //invalid token error

    }

    public function a_legitimate_token_is_used_to_access_private_page(){
        //redundant but easy    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(200)
		->assertJsonCount(1, 'jwt');

        $token = $this->response->getOriginalContent()->getData();
    	//evaluate token as needed
        $response = $this->call('POST', 'user/user-data.php', ['jwt'=>$token]);
	$response
		->assertStatus(200);
    }

    public function a_token_is_expired_when_used_on_private_page(){
            //redundant but easy    
	$response = $this->call('POST', 'user/register.php', ['name'=>'test', 'password'=>'hashedtestpass', 'email'=>'adtest10080@gmail.com']);
	$response
		->assertStatus(200)
		->assertJson([
                'created' => '1',
            ]);

	$response = $this->call('POST', 'user/login.php', ['name'=>'test', 'password'=>'hashedtestpass']);
        $response
		->assertStatus(200)
		->assertJsonCount(1, 'jwt');

        $token = $this->response->getOriginalContent()->getData();
    	//evaluate token as needed
        $response = $this->call('POST', 'user/user-data.php', ['jwt'=>$token]);
	$response
		->assertStatus(401)
		->assertJson('access'=>'-1');

    }

}
