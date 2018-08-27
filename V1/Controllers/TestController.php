<?php
    /**
     * TestController.php
     *
     * Created by PhpStorm.
     * author: liuml  
     * DateTime: 2018/8/20  10:02
     */

    namespace App\WechatXiaowei\V1\Controllers;

    use App\WechatXiaowei\V1\Services\wechat;
    use App\WechatXiaowei\V1\Services\wechatCertificate;

    class TestController extends BaseController
    {
        public function index(){
            return [
                'code' => 1,
                'message' => '操作成功',
                'data' => 111
            ];
        }

    }