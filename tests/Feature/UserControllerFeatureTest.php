<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserControllerFeatureTest extends TestCase {
    use RefreshDatabase;

    public $mockConsoleOutput = false;
    // This test won't work as i had troubles with client secrets (oauth) and Passport, then with the
    // laravel Artisan commands. Every documentation i read explained a solution that seems to dont exist anymore

    // protected function setUp(): void {
    //     parent::setUp();
    //     Artisan::call('passport:client', ['--personal' => null, '--no-interaction' => true]);
    // }

    // public function testRegisterUserApiSuccessful(): void {
    //     $response = $this->postJson(
    //         '/api/register',
    //         [
    //             'name' => 'Test Name',
    //             'email' => 'testemail@gmail.com',
    //             'password' => 'testpassword123__'
    //         ]
    //     );
    //     $response->assertStatus(200);
    //     $this->assertTrue($response['token'] !== null);
    // }

    // public function testRegisterUserApiError(): void {
    //     $response = $this->postJson(
    //         '/api/register',
    //         [
    //             'name' => 'Test Name',
    //             'email' => 'testemailwrongemail',
    //             'password' => '1'
    //         ]
    //     );
    //     $response->assertStatus(200);
    //     $this->assertTrue(!isset($response['token']));
    //     $this->assertEquals('Data validation error', $response['message']);
    //     $this->assertEquals('The email field must be a valid email address.', $response['errors'['email']]);
    //     $this->assertEquals('The password field must be at least 8 characters.', $response['errors'['password']]);
    // }

    // For login tests, i might have to Mock something, like the User model. To avoid a real DB check

    // public function testLoginUserApiSuccessful(): void {
    //     $response = $this->postJson(
    //         '/api/login',
    //         [
    //             'email' => 'testemail@gmail.com',
    //             'password' => 'testpassword123__'
    //         ]
    //     );
    //     $response->assertStatus(200);
    //     $this->assertTrue($response['token'] !== null);
    // }

    // public function testLoginUserApiError(): void {
    //     $response = $this->postJson(
    //         '/api/login',
    //         [
    //             'email' => 'testemail@gmail.com',
    //             'password' => 'wrongpass'
    //         ]
    //     );
    //     $this->assertTrue(!isset($response['token']));
    //     $this->assertEquals('Email and Password don\'t match', $response['message']);
    // }
}
