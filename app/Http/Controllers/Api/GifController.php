<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RUserGif;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Response as ClientResponse;

class GifController extends Controller {
    private const GIPHY_API_KEY = '8BUVuPv4FWV4QqNGskbGnLXoFZyrmmuS';
    private const GIPHY_MAIN_URL = 'https://api.giphy.com/v1/gifs/';

    /**
     * This method returns a JSON object with GIFObjects from Giphy inside of it.
     * This object's most important attributes are: ID, a URL to the original GIF,
     * an images object with different sizes to use (each with its URL).
     */
    public function getGifByStringSearch(Request $request): JsonResponse {
        try {
            if ($errorResponse = $this->validateDataAndFailIfNeeded(
                $request,
                [
                    'searchString' => 'required|string',
                    'limit' => 'sometimes|integer',
                    'offset' => 'sometimes|integer'
                ]
            )) {
                return $errorResponse;
            }
            $giphyResponse = Http::get(static::GIPHY_MAIN_URL . 'search', [
                'api_key' => static::GIPHY_API_KEY,
                'q' => $request->searchString,
                'limit' => $request->limit,
                'offset' => $request->offset
            ]);
            $response = $this->getGiphyResponse($giphyResponse);
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error obtaining gifs',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * This method searches for a specific GIF using its ID.
     */
    public function getGifById(Request $request): JsonResponse {
        try {
            if ($errorResponse = $this->validateDataAndFailIfNeeded(
                $request,
                [
                    'gifId' => 'required|string'
                ]
            )) {
                return $errorResponse;
            }
            $giphyResponse = $this->doGetGifById($request->gifId);
            $response = $this->createResponseForUser($giphyResponse);
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error obtaining gifs',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Saves a GIF as favourite for a specific User.
     */
    public function saveGifAsFavourite(Request $request): Response|JsonResponse {
        try {
            if ($errorResponse = $this->validateDataAndFailIfNeeded(
                $request,
                [
                    'userId' => 'required|integer',
                    'gifId' => 'required|string',
                    'alias' => 'required|string'
                ]
            )) {
                return $errorResponse;
            }
            // Validating if the user and gif actually exist
            $gifRequest = $this->doGetGifById($request->gifId);
            $user = User::where('id', $request->userId)->first();
            if ($user && $gifRequest->successful()) {
                // Check if the GIF is already marked as favourite for that user
                if (
                    RUserGif::where('user_id', $request->userId)
                        ->where('gif_id', $request->gifId)
                        ->first()
                ) {
                    return response()->json([
                        'message' => 'GIF already marked as favourite for that user'
                    ], 401);
                }
                RUserGif::create([
                    'user_id' => $request->userId,
                    'gif_id' => $request->gifId,
                    'alias' => $request->alias
                ]);
                return response()->noContent(200);
            }
            return response()->json([
                'message' => 'Provided User and/or GIF not found'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error saving favourite gif',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    private function doGetGifById(string $gifId): ClientResponse {
        return Http::get(static::GIPHY_MAIN_URL . $gifId, [
            'api_key' => static::GIPHY_API_KEY,
        ]);
    }

    /**
     * Arranging the object that will be returned from this endpoint
     */
    private function createResponseForUser(ClientResponse $giphyResponse): array {
        if ($giphyResponse->successful()) {
            $response = [
                'data' => $giphyResponse->body()
            ];
        } else {
            $response = [
                'message' => 'Something went wrong while searching in Giphy',
                'errors' => $giphyResponse->body()
            ];
        }
        return $response;
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
