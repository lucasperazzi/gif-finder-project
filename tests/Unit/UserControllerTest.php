<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    // Tried to make this test work, but as the create method from the User model is static,
    // i was not able to mock it. With more time for investigation i think i could have made it work


    // private $mockedUserModel;
    // private $userController;

    // protected function setUp(): void {
    //     $this->mockedUserModel = $this->createMock(User::class);
    //     $this->mockedUserModel->expects($this->any())
    //         ->method('create')
    //         ->willReturn(['test']);
    //     $this->userController = new UserController($this->mockedUserModel);
    // }

    // public function testLoginUser(): void {
    //     $request = Request::create('/api/login', 'POST', [
    //         'email' => 'user@test.com',
    //         'password' => 'testpassword_123',
    //     ]);
    //     $response = $this->userController->registerUser($request);
    //     $this->assertTrue(true);
    // }
}
