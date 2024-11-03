<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

abstract class Controller {
    /**
     * Validates the data sent by the user
     * @param Request $request The original user sent request with data to validate
     * @param array $paramsToCheck An array with: params => rules to check
     */
    abstract protected function validateDataAndFailIfNeeded(Request $request, array $paramsToCheck): ?JsonResponse;
}
