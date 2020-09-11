<?php



use App\Http\Controllers\PageGenerationController;
use App\Http\Controllers\UserCreationController;
use App\Http\Controllers\UserSignInController;

use Tests\TestCase;
use App\User;
use App\Mods;
use App\Bans;
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
	    Storage::fake('local');
	    App\Http\Controllers\PageGenerationController::CreateUserFile("test");
	    Storage::disk('local')->assertExists("test.json");
    }
        public function test_make_user_json_file_fails(){
	    Storage::fake('local');
	    App\Http\Controllers\PageGenerationController::CreateUserFile("image/test");
	    Storage::disk('local')->assertMissing("image/test.json");
    }



    public function test_add_user_data(){
	    App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    $this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedpass']) != false);
    }
    public function test_add_user_data_fails_invalid_char(){
	    App\Http\Controllers\UserCreationController::addNewUserToDB("tes;t", "hashedpass");
	    $this->assertFalse(Auth::attempt(['name'=>'tes;t', 'password'=>'hashedpass']) != false);
    }
    public function test_add_user_data_fails_bad_name(){
	    App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    $this->assertFalse(Auth::attempt(['name'=>'tjest', 'password'=>'hashedpass']) != false);
    }
    public function test_add_user_data_fails_bad_pass(){
	    App\Http\Controllers\UserCreationController::addNewUserToDB("test", "hashedpass");
	    $this->assertFalse(Auth::attempt(['name'=>'test', 'password'=>'hashed;pass']) != false);
    }


    // route tests
    public function test_post_to_create_returns_non_404_respsonse(){
	    $response = $this->json('POST', 'api/create', []);
	    $this->assertEquals(422, $response->status() );
    }
    public function test_post_to_login_returns_non_404_response(){
	$response = $this->json('POST', 'api/login', []);
	$this->assertEquals(422, $response->status() );
    }

    //db tests
    public function test_user_in_DB(){
	$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
	$user->save();
	$this->assertTrue(Auth::attempt(['name'=>'test', 'password'=>'hashedpass']) != false
		&& Auth::attempt(['name'=>'test', 'password'=>'hashedpass222']) == false);
    }
    public function test_user_not_in_DB(){
	$this->expectException(\PDOException::class);
	$user = new User (['name' => "test"]);
	$user->save();
    }

    // jwt auth tests 
    public function test_JWT_return_fail_not_existant(){
	    $this->assertTrue(App\Http\Controllers\UserSignInController::returnJWT("testname", "pass") == false);
    }
        // jwt auth tests 
    public function test_JWT_return_fail_missing_param_name(){
	    $this->expectException(ArgumentCountError::class);
	    $this->assertTrue(App\Http\Controllers\UserSignInController::returnJWT("pass") == false);
    }
        // jwt auth tests 
    public function test_JWT_return_fail_empty_param_pass(){
	    $this->assertTrue(App\Http\Controllers\UserSignInController::returnJWT("testname", "") == false);
    }

    public function test_access_to_restricted(){
	    $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/details');
	    $this->assertEquals(401, $response->status() );
    }

    //middleware and db describe test
	public function test_a_user_is_mod(){
	    	$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		$mod = new Mods(['fk_name'=>'test']);
		$mod->save();
		
		$this->assertTrue($user->isMod());
	}
    	public function test_a_user_is_banned(){
	    	$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		$b = new Bans(['fk_name'=>'test']);
		$b->save();
		
		$this->assertTrue($user->isBanned());
	}

	public function test_can_not_access_user_details_api_natural(){
		
		$response = $this->json('GET', 'api/details', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['message'=>"Unauthenticated."]);	
	}

	public function test_can_not_access_user_ad_create_api_natural(){
		
		$response = $this->json('POST', 'api/details', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['message'=>"Unauthenticated."]);		
	}

	public function test_can_not_access_user_removal_api_natural(){
		
		$response = $this->json('POST', 'api/removal', ['name'=>'test', 'pass'=>'hashedpass']);
		$this->assertEquals(json_decode($response->getContent(), true), ['message'=>"Unauthenticated."]);	
	}

	public function test_can_not_access_user_login_api_banned(){
		
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		$b = new Bans(['fk_name'=>'test']);
		$b->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You've been banned..."]);
	}

	public function test_can_not_access_user_details_api_banned(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
	
		$b = new Bans(['fk_name'=>'test']);
		$b->save();
		
		$response = $this->call('GET', 'api/details', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You've been banned..."]);
	}

	public function test_can_not_access_user_ad_create_api_banned(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		$b = new Bans(['fk_name'=>'test']);
		$b->save();
		
		$response = $this->call('POST', 'api/details', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You've been banned..."]);
	}

	public function test_can_not_access_user_removal_api_banned(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);

		$b = new Bans(['fk_name'=>'test']);
		$b->save();
		
		$response = $this->call('POST', 'api/removal', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You've been banned..."]);	
	}

	public function test_can_not_access_mod_login_api_normal(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/mod/login', ['name'=>'test', 'pass'=>'hashedpass']);
		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You are not a moderator"]);
	}

	public function test_can_not_access_mod_all_api_normal(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		
		$response = $this->call('GET', 'api/mod/all', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You are not a moderator"]);
	}

	public function test_can_not_access_mod_ban_api_normal(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		
		$response = $this->call('POST', 'api/mod/ban', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You are not a moderator"]);
	}

	public function test_can_not_access_mod_remove_all_api_unmod(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		$response = $this->call('POST', 'api/mod/purge', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You are not a moderator"]);	
	}
	public function test_can_not_access_mod_remove_ind_api_unmod(){
		$user = new User (['name' => "test", 'pass' => bcrypt("hashedpass")]);
		$user->save();
		
		$response = $this->call('POST', 'api/login', ['name'=>'test', 'pass'=>'hashedpass']);
		$response = $this->call('POST', 'api/mod/individual', ['name'=>'test', 'pass'=>'hashedpass']);

		$this->assertEquals(json_decode($response->getContent(), true), ['warn'=>"You are not a moderator"]);	
	}



  }
