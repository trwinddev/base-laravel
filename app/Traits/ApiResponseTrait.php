<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

trait ApiResponseTrait
{

    private $errorCode = [
        Response::HTTP_BAD_REQUEST => 'Bad Request',
        Response::HTTP_UNAUTHORIZED => 'Unauthorized',
        Response::HTTP_FORBIDDEN => 'Forbidden',
        Response::HTTP_NOT_FOUND => 'Not Found',
        Response::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        Response::HTTP_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        Response::HTTP_TOO_MANY_REQUESTS => 'Too Many Requests',
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
    ];

    protected function responseSuccess($data = null, $code = 200)
    {

        $response = [
            "msg" => "success",
            "code" => $code,
            "data" => $data
        ];

        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseSuccessNoData($code = 200)
    {

        $response = [
            "msg" => "success",
            "code" => $code
        ];

        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseError($code = 500, $message = null, $data = null)
    {

        $error_message = (isset($this->errorCode[$code]) && empty($message)) ?  $this->errorCode[$code] : $message;

        $response = [
            "msg" => "error",
            "code" => $code,
            "message" => $error_message
        ];

        if ($data) {
            $response["data"] = $data;
        }

        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseInvalidParameters($error_message, $code = 400)
    {

        // $errors = (isset($this->errorCode[$code])) ?  $this->errorCode[$code] : 'Invalid Parameters';

        $response = [
            "msg" => "error",
            "code" => $code,
            "message" => $error_message,
            "errors" => 'Invalid Parameters'
        ];

        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseErrorMessageWithCode($message = null, $code = 400, $codeMsg = '')
    {
        $response = [
            "msg" => "error",
            "code" => $code,
            "code_msg" => $codeMsg == '' ? $code : $codeMsg,
            "message" => $message
        ];
        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseErrorMessage($message = null, $code = 400)
    {

        $response = [
            "msg" => "error",
            "code" => $code,
            "message" => $message
        ];
        return response()->json($response, $code)
            ->header('X-XSS-Protection', '1; mode=block');
    }

    protected function responseValidateForm($dataValiadte, $rulesValiadte, $messageValiadte = [], $niceNames = [])
    {
        $errData = [
            "msg" => "success",
        ];

        $validator = Validator::make($dataValiadte, $rulesValiadte, $messageValiadte, $niceNames);
        if ($validator->fails()) {
            $errData["msg"] = "error";
            $messages = $validator->errors()->messages();
            foreach ($messages as $colKey => $msg) {
                $errData["errors"][$colKey] = $msg[0];
            }
        }

        $responseData["msg"] = $errData["msg"];

        if (!$errData["msg"]) {
            $response = [
                "msg" => "error",
                "code" => 400,
                "errorValidate" => true,
                "errors" => $errData["errors"],
            ];
            $responseData["data"] = response()->json($response, 400)
                ->header('X-XSS-Protection', '1; mode=block');
        }

        return $responseData;
    }

}
