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

class UserController extends Controller
{
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
                'message' => 'User created correctly',
                'token' => $user->createToken('USER-TOKEN')->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            // Catching any possible error
            return response()->json([
                'message' => 'Error creating user',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

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
            return response()->json([
                'message' => 'User logged in correctly',
                'token' => $user->createToken('USER-TOKEN')->plainTextToken
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error logging user',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Validating the data sent by the user
     */
    private function validateDataAndFailIfNeeded(Request $request, array $paramasToCheck): ?JsonResponse {
        $validation = Validator::make($request->all(), $paramasToCheck);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Data validation error',
                'errors' => $validation->errors()
            ], 401);
        }
        return null;
    }
}
