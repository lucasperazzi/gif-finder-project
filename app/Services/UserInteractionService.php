<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\UserInteraction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class UserInteractionService {
    /**
     * This method register each user interaction with any service.
     */
    public function createUserInteraction(Request $request, int $httpCode, ?array $response): void {
        try {
            $userId = Auth::user()->id;
            UserInteraction::create([
                'user_id' => $userId,
                'used_service' => Route::current()->uri() ?: 'unknown',
                // We could perform some cleanup of the petition's body
                'petition_body' => $request->getContent(),
                'petition_origin_ip' => $request->ip(),
                'response_http_code' => strval($httpCode),
                'response_body' => json_encode($response) ?? ''
            ]);
        } catch (\Throwable $th) {
            // Logging the error as its not as important to break the execution. You can see this in /storage/logs/laravel.log
            Log::error('Error trying to save user interaction: ' . $th->getMessage(), ['exception' => $th]);
        }
    }
}
