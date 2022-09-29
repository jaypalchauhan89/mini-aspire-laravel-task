<?php

namespace App\Traits;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait ApiResponser{

    /**
     * success response method.
     *
     * @param $result
     * @param $message
     *
     * @return JsonResponse
     */
    public function successResponse($result,$message='',$code=200)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param  array  $errorMessages
     * @param  int  $code
     *
     * @return JsonResponse
     */
    public function errorResponse($message='', $errorMessages = [], $code = 200)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        $response['errors'] = $errorMessages;
        /*if (count($errorMessages)) {
            $response['errors'] = $errorMessages;
        }*/

        return response()->json($response, $code);
    }
}