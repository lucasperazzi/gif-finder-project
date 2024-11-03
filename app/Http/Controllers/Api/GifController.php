<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RUserGif;
use App\Models\User;
use App\Services\UserInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Response as ClientResponse;

class GifController extends Controller {
    /**
     * This API key would usually be part of the .env file, to avoid commiting this directly into github
     * but i'm leaving it here to avoid people testing it to create their own API keys.
     */
    private const GIPHY_API_KEY = '8BUVuPv4FWV4QqNGskbGnLXoFZyrmmuS';
    private const GIPHY_MAIN_URL = 'https://api.giphy.com/v1/gifs/';
    private $userInteractionService;

    /**
     * Creates a new GIF Controller
     * @param UserInteractionService $userInteractionService An injected service that will register every user interaction
     */
    public function __construct(UserInteractionService $userInteractionService) {
        $this->userInteractionService = $userInteractionService;
    }

    /**
     * Returning a JSON object with GIFObjects from Giphy inside of it.
     * This object's most important attributes are: ID, a URL to the original GIF,
     * an "images" object with different sizes to use (each with its URL).
     * @param Request $request The user sent request with the Giphy query params
     * @return JsonReponse With the data retrieved from Giphy, or an error message
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
            $response = $this->createResponseForUser($giphyResponse);
            $this->userInteractionService->createUserInteraction($request, 200, $response);
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Error obtaining gifs',
                'error' => $th->getMessage()
            ];
            $this->userInteractionService->createUserInteraction($request, 500, $response);
            return response()->json($response, 500);
        }
    }


    /**
     * Searches for a specific GIF using its ID.
     * @param Request $request The user sent request with the GIF ID
     * @return JsonResponse With the found GIF data, or an error message.
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
            $this->userInteractionService->createUserInteraction($request, 200, $response);
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Error obtaining gifs',
                'error' => $th->getMessage()
            ];
            $this->userInteractionService->createUserInteraction($request, 500, $response);
            return response()->json($response, 500);
        }
    }

    /**
     * Saves a GIF as favourite for a specific User.
     * @param Request $request User sent request with userId, gifId and alias
     * @return Response|JsonResponse No data if it works,or an error message if needed.
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
                    RUserGif::where('user_id', $request->userId)->where('gif_id', $request->gifId)->first()
                ) {
                    $response = ['message' => 'GIF already marked as favourite for that user'];
                    $this->userInteractionService->createUserInteraction($request, 401, $response);
                    return response()->json($response, 401);
                }
                RUserGif::create([
                    'user_id' => $request->userId,
                    'gif_id' => $request->gifId,
                    'alias' => $request->alias
                ]);
                $this->userInteractionService->createUserInteraction($request, 200, null);
                return response()->noContent(200);
            }
            $response = ['message' => 'Provided User and/or GIF not found'];
            $this->userInteractionService->createUserInteraction($request, 401, $response);
            return response()->json($response, 401);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Error saving favourite gif',
                'error' => $th->getMessage()
            ];
            $this->userInteractionService->createUserInteraction($request, 500, $response);
            return response()->json($response, 500);
        }
    }

    /**
     * Performs the GET operation to the Giphy API (search by id)
     * @param string $gifId The GIF's ID to find
     * @return ClientResponse The Giphy API response
     */
    private function doGetGifById(string $gifId): ClientResponse {
        return Http::get(static::GIPHY_MAIN_URL . $gifId, [
            'api_key' => static::GIPHY_API_KEY,
        ]);
    }

    /**
     * Arranges the object that will be returned from this endpoint
     * @param ClientResponse $giphyResponse The API response we got
     * @return array An array with data or an error message if needed
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
     * Validates the data sent by the user
     * @param Request $request The original user sent request with data to validate
     * @param array $paramsToCheck An array with: params => rules to check
     */
    protected function validateDataAndFailIfNeeded(Request $request, array $paramsToCheck): ?JsonResponse {
        $validation = Validator::make($request->all(), $paramsToCheck);
        if ($validation->fails()) {
            $response = [
                'message' => 'Data validation error',
                'errors' => $validation->errors()
            ];
            $this->userInteractionService->createUserInteraction($request, 401, $response);
            return response()->json($response, 401);
        }
        return null;
    }
}
