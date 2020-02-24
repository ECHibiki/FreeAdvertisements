<?php



require "app/Http/Controllers/PageGenerationController.php";
require "app/Http/Controllers/UserCreationController.php";
require "app/Http/Controllers/UserSignInController.php";

use Tests\TestCase;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use Auth;

class DeveloperUserAccessTests extends TestCase
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

// user creation tests




    public function test_make_user_json_file(){
	    App\Http\Controllers\PageGenerationController::CreateUserFile("test");
	    $this->assertTrue(file_exists("resources/user-json/test.json"));
	    unlink("resources/user-json/test.json");
    }

    public function test_add_user_data(){
	    App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    $this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedpass']) != false);
    }


    public function test_post_to_create_returns_non_404_respsonse(){
	    $response = $this->call('POST', 'api/create', []);
	    $this->assertEquals(401, $response->status() );
    }

    //login tests
    public function test_post_to_login_returns_non_404_response(){
	$response = $this->call('POST', 'api/login', []);
	$this->assertEquals(401, $response->status() );
    }

    public function test_user_in_DB(){
	$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
	$user->save();
	$this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedpass']) != false
		&& Auth::attempt(['name'=>'test', 'password'=>'hashedpass222']) == false);
    }

  // jwt auth tests 


    public function test_JWT_return(){
	    $this->assertTrue(App\Http\Controllers\UserSignInController::returnJWT("testname", "pass") == false);
    }


    public function test_access_to_restricted(){
	    $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/details');
	    $this->assertEquals(401, $response->status() );
    }

  }
