<?php
    /**
     * WxException.php
     *
     * Created by PhpStorm.
     * author: liuml  <liumenglei0211@163.com>
     * DateTime: 2018/8/24  16:17
     */

    namespace App\WechatXiaowei\V1\Exception;
    
    use Illuminate\Support\Facades\Config;

    class WxException extends \Exception
    {
        public static $errorMessage = [];

        public static $defaultMessage = '未知错误';

        public function __construct($code = 0, $message = "")
        {
            if ($message === "") {
                self::$errorMessage = Config::get('errorCode.wxError');
                $message            = static::$errorMessage[$code] ?? static::$defaultMessage;
            }
            parent::__construct($message, $code);
        }

        public function getName()
        {
            return 'WxException';
        }

        public function __toString()
        {
            return json_encode($this->getResponse());
        }

        public function getResponse()
        {
            return $this->response;
        }
    }