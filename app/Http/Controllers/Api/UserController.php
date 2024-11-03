<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class UserController extends Controller {
    /**
     * Registers a new User in the system
     * @param Request $request The user sent request
     * @return JsonReponse A json response with the user token or an error message
     */
    public function registerUser(Request $request): JsonResponse {
        try {
            // Validating user data
            if ($errorResponse = $this->validateDataAndFailIfNeeded(
                $request,
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => ['required', Rules\Password::defaults()]
                ]
            )) {
                return $errorResponse;
            }
            // If it passed the validation, create the user
            $user  = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            return response()->json([
                'token' => $user->createToken('USER-TOKEN')->accessToken
            ], 200);
        } catch (\Throwable $th) {
            // Catching any possible error
            return response()->json([
                'message' => 'Error creating user',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Logs in a user into the system
     * @param Request $request The user sent request with the credentials
     * @return JsonResponse With an authentication token or an error message
     */
    public function loginUser(Request $request): JsonResponse {
        try {
            // Validating user data
            if ($errorResponse = $this->validateDataAndFailIfNeeded(
                $request,
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            )) {
                return $errorResponse;
            }
            // If the authentication login fails, return an error
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'message' => 'Email and Password don\'t match'
                ], 401);
            }
            // Finding the User, and creating a token
            $user = User::where('email', $request->email)->first();
            return response()->json(['token' => $user->createToken('USER-TOKEN')->accessToken], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error logging user',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Gets an array with all registered users in the system
     * This method was added for testing purposes.
     * @param Request $request The user sent request
     * @return JsonResponse With an array of "data" with all users, or an error message
     */
    public function getAllUsers(Request $request): JsonResponse {
        try {
            $users = User::all();
            return response()->json([
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error obtaining users',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Searches a User by its ID
     * @param int $userId The ID of the User to search
     * @return User|null The user if found, and NULL if not
     */
    public function getUserById(int $userId): ?User {
        return User::where('id', $userId)->first();
    }

    /**
     * Validates the data sent by the user
     * @param Request $request The original user sent request with data to validate
     * @param array $paramsToCheck An array with: params => rules to check
     */
    protected function validateDataAndFailIfNeeded(Request $request, array $paramsToCheck): ?JsonResponse {
        $validation = Validator::make($request->all(), $paramsToCheck);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Data validation error',
                'errors' => $validation->errors()
            ], 401);
        }
        return null;
    }
}
