<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    /*
    |--------------------------------------------------------------------------
    | api 响应
    |--------------------------------------------------------------------------
    |api 响应格式说明
    |{
    |   "status":"",        必须返回，只能为 success 或者 error
    |   "code":"",          必须返回，http状态码
    |   "message":"",       可选返回，作为请求成功 或者 失败的一个备注说明
    |   "data":"",          可选返回，请求成功需要返回数据时返回
    |   "errors":""         可选返回，请求失败返回的具体错误信息说明
    |   "validations":""    可选返回，表单验证失败结果数据
    |}
    |
    */

    protected $successStatus = 'success';
    protected $errorStatus = 'error';
    protected $statusName = '';
    protected $statusCode = 0;
    protected $message = '';
    protected $data = [];
    protected $errors = [];
    protected $validation = false;

    protected function response()
    {
        $data = [
            'status' => $this->statusName,
            'code' => $this->statusCode,
        ];

        if ($this->message !== '') {
            $data = array_merge($data, ['message' => $this->message]);
        }

        if ($this->data !== []) {
            $data = array_merge($data, ['data' => $this->data]);
        } elseif ($this->errors !== []) {
            $data = array_merge($data, [$this->validation ? 'validations' : 'errors' => $this->errors]);
        }

        return response()->json($data, $this->statusCode);
    }

    /**
     * @param mixed ...$args
     * @return \Illuminate\Http\JsonResponse
     * @author King
     * @version 2019/5/30 0:09
     */
    public function success(...$args)
    {
        $this->statusName = $this->successStatus;
        $this->statusCode = Response::HTTP_OK;

        collect($args)->each(function ($arg) {
            if (is_string($arg)) {
                $this->message = $arg;
            } elseif (is_int($arg)) {
                $this->statusCode = $arg;
            } else {
                $this->data = $arg;
            }
        });

        return $this->response();
    }

    /**
     * @param mixed ...$args
     * @return \Illuminate\Http\JsonResponse
     * @author King
     * @version 2019/5/30 0:11
     */
    public function failure(...$args)
    {
        $this->statusName = $this->errorStatus;
        $this->statusCode = Response::HTTP_BAD_REQUEST;

        collect($args)->each(function ($arg) {
            if (is_string($arg)) {
                $this->message = $arg;
            } elseif (is_int($arg)) {
                $this->statusCode = $arg;
            } elseif (is_bool($arg)) {
                $this->validation = $arg;
            } else {
                $this->errors = $arg;
            }
        });

        return $this->response();
    }

    /**
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:07
     */
    public function created(array $data = [])
    {
        return $this->success($data, Response::HTTP_CREATED);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:18
     */
    public function failed()
    {
        return $this->failure();
    }

    /**
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/25 18:25
     */
    public function failedWithMessage(string $message, int $code = Response::HTTP_BAD_REQUEST)
    {
        return $this->failure($message, $code);
    }

    /**
     * @param array $errors
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/25 18:25
     */
    public function failedWithErrors(array $errors, int $code = Response::HTTP_BAD_REQUEST)
    {
        return $this->failure($errors, $code);
    }

    /**
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param bool $validation
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/25 17:37
     */
    public function failedWithMessageAndErrors(string $message, array $errors, int $code = Response::HTTP_BAD_REQUEST, bool $validation = false)
    {
        if ($code === Response::HTTP_NOT_FOUND){
            return $this->failure(Response::$statusTexts[$code], $errors, $code);
        }

        return $this->failure($message, $errors, $code, $validation);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:18
     */
    public function successful()
    {
        return $this->success();
    }

    /**
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:10
     */
    public function successWithData(array $data)
    {
        return $this->success($data);
    }

    /**
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:13
     */
    public function successWithMessage(string $message)
    {
        return $this->success($message);
    }

    /**
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     * @author: King
     * @version: 2019/7/23 12:13
     */
    public function successWithMessageAndData(string $message, array $data)
    {
        return $this->success($message, $data);
    }
}
