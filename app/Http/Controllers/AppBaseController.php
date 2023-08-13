<?php

namespace App\Http\Controllers;

use App\Traits\Arr;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;

/**
 * @OA\Server(url="/api")
 * @OA\Info(
 *   title="InfyOm Laravel Generator APIs",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    public function sendApiError($errors, $code = 404)
    {
        return Response::json([
            'success' => false,
            'message' => 'Validation errors',
            'data'    => $errors,
        ], $code);
    }

    public function sendSuccess($message)
    {
        return Response::json([
            'success' => true,
            'message' => $message,
        ], 200);
    }

    public function getInput($request)
    {
        // $input    = Arr::underscoreKeys($request->except(['current', 'pageSize']));
        $input = $request->all();
        $current = $request->get('current');
        $current = isset($current) ? $current - 1 : 0;
        $pageSize = $request->get('pageSize') ?? 10;

        return [$input, $current * $pageSize, $pageSize];
    }
}
