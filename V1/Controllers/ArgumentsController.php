<?php
    /**
     * TestController.php
     *
     * Created by PhpStorm.
     * author: liuml
     * DateTime: 2018/8/20  10:02
     */

    namespace App\WechatXiaowei\V1\Controllers;

    class ArgumentsController extends BaseController
    {
        /**
         * getErrorCode    获取错误提示列表
         * @return \Illuminate\Http\JsonResponse
         */
        public function getErrorCode()
        {
            $error = \Config::get('errorCode.wxError');
            return static::returnData(1, '', $error);
        }

        /**
         * getRateList    费率枚举值
         * @return \Illuminate\Http\JsonResponse
         */
        public function getRateList()
        {
            $rateList = ['0.38%', '0.39%', '0.4%', '0.45%', '0.48%', '0.49%', '0.5%', '0.55%', '0.58%', '0.59%', '0.6%'];
            return static::returnData(1, '', $rateList);
        }

    }