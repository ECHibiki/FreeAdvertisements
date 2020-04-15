<?php

namespace Tests\Unit;


use Tests\TestCase;
use App\User;
use App\Bans;
use App\Mods;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Auth;
use App\Mail\BannerNotification;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;

class DeveloperMailTests extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
    //test email view exists
    // Determining if an email is embeded or not is hard, but at least it should stay same character length
	public function test_email_view_propper(){

    //var_dump((new \App\Mail\BannerNotification(["name"=>"testname", "time"=>date('y',time()), "url"=>"http://sdf.com", 'err'=>'',  'fname'=>'notimage']))
    //    ->render());
    // It's known from ^ that the email body will be 200 characters long
		$this->assertEquals(strlen((new \App\Mail\BannerNotification(["name"=>"testname", "time"=>date('y',time()), "url"=>"http://sdf.com", 'err'=>'',  'fname'=>'notimage']))
        ->render()) , 200
    );
	}

	//test sending email
	public function test_sending_email(){
		Mail::fake();
		$re = \App\Http\Controllers\MailSendController::sendMail(["name"=>"testname", "time"=>date('yM d-h:m:s',time()), "url"=>"http://sdf.com", 'fname'=>'notimage'],
			['primary_email'=>env('PRIMARY_MOD_EMAIL'), 'secondary_emails'=>env('SECONDARY_MOD_EMAIL_LIST')]);
		$this->assertEquals($re, true);
		Mail::assertSent(BannerNotification::class);
	}

	public function test_setting_cooldown(){
		Storage::fake('local');
		\App\Http\Controllers\MailSendController::updateCooldown();
		Storage::disk('local')->assertExists('mail/mail.json');
		$file = Storage::disk('local')->get('mail/mail.json');
		sleep(1);
		\App\Http\Controllers\MailSendController::updateCooldown();
		$this->assertNotEquals($file, Storage::disk('local')->get('mail/mail.json'));
	}

	public function test_getting_cooldown(){
		Storage::fake('local');
		$cd = \App\Http\Controllers\MailSendController::getCooldown();
		$this->assertEquals($cd, 0);
	}

	// not having an email doesn't cause error
	public function test_email_does_not_error_when_no_emails_listed(){
		Mail::fake();
		$re = \App\Http\Controllers\MailSendController::sendMail(["name"=>"testname", "time"=>date('yMd-h:m:s',time()), "url"=>"http://sdf.com"],
			['primary_email'=>null, 'secondary_emails'=>env('SECONDARY_MOD_EMAIL_LIST')]);
		$this->assertEquals($re, false);
		Mail::assertNothingSent();
	}
}
