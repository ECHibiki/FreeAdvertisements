<?php



require "app/Http/Controllers/PageGenerationController.php";
require "app/Http/Controllers/UserCreationController.php";


use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

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

    public function test_make_user_json_file(){
	    App\Http\Controllers\PageGenerationController::CreateUserFile("test");
	    $this->assertTrue(file_exists("resources/user-json/test.json"));
	    unlink("resources/user-json/test.json");
    }

    public function test_add_user_data(){
    	App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	$this->assertDatabaseHas('free_ad_users', [
            'name'=>'test', 'pass'=>'hashedpass'
    	]);
    }

    public function test_post_to_creation_returns_non_404_respsonse(){
	    $response = $this->call('POST', 'create', []);
	    $this->assertTrue($response->status() != 404 && $response->status() != 500);
    }
}
