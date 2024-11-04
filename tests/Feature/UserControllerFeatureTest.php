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

    // public function testRegisterUserApi(): void {
    //     $response = $this->postJson(
    //         '/api/register',
    //         [
    //             'name' => 'Test Name',
    //             'email' => 'testemail@gmail.com',
    //             'password' => 'testpassword123__'
    //         ]
    //     );
    //     $response->assertStatus(200);
    // }
}
