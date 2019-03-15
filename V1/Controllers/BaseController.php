<?php

    namespace App\WechatXiaowei\V1\Controllers;

    use App\Http\Controllers\Controller;
    use Dingo\Api\Routing\Helpers;

    class BaseController extends Controller
    {
        use Helpers;

        /**
         * 接口全局统一格式返回
         * @param string $code
         * @param string $status
         * @param array $data
         * @param string $msg
         * @return \Illuminate\Http\JsonResponse
         * author Fox
         */
        public static function returnData($code = 0, $msg = '', $data = '', $status = 200)
        {
            if (empty($msg)) {
                $msg = config('errorCode.wxError.' . $code);
            }
            if (!empty($data) && !is_array($data)) {
                $result = [
                    'res' => $data,
                ];
            }
            return response()->json([
                'code'    => $code,
                'message' => $msg,
                'data'    => $result ?? $data ? : [],
            ], $status);
        }
    }
