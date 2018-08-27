<?php
    /**
     * UploadMedia.php
     *
     * Created by PhpStorm.
     * author: liuml  
     * DateTime: 2018/8/24  13:59
     */

    namespace App\WechatXiaowei\V1\Services\Traits;


    // use App\Helpers\Log;
    use App\WechatXiaowei\V1\Exception\WxException;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\Facades\Request;

    trait UploadMedia
    {
        protected $media_addr;

        public function uploadImg()
        {
            $url = self::WXAPIHOST . 'secapi/mch/uploadmedia';
            // 判断图片地址是否为空，空的话就调用图片上传方法，把图片上传到服务器
            empty($this->media_addr) && $this->saveImg();
            // 判断图片是否存在
            if (!file_exists($this->media_addr))
                throw new WxException(10001);
            $data              = [
                'mch_id'     => $this->mch_id,
                'media_hash' => md5_file($this->media_addr),
            ];
            $data['sign_type'] = 'HMAC-SHA256';
            $data['sign']      = $this->makeSign($data, $data['sign_type']);
            // CURLFile 类的解释 http://php.net/manual/zh/class.curlfile.php
            $data['media'] = new \CURLFile($this->media_addr);
            $header        = [
                "content-type:multipart/form-data",
            ];
            $res           = $this->httpsRequest($url, $data, $header, true);
            // 处理返回值
            return $this->disposeReturn($res, ['media_id']);
        }

        public function saveImg()
        {
            $images = Request::file('media'); //1、使用laravel 自带的request类来获取一下文件
            if (!$images) {
                \Log::info('saveImg' . microtime(true));
                throw new WxException(0, '至少上传一张图片');
            }
            $uploadMediaAddr = Config::get('api.wechatConfig.uploadMediaAddr'); //2、定义图片上传路径
            $imagesName      = $images->getClientOriginalName(); //3、获取上传图片的文件名
            $extension       = $images->getClientOriginalExtension(); //4、获取上传图片的后缀名
            if (in_array($extension, ['jpeg', 'jpg', 'bmp', 'png'])) {
                $newImagesName = md5(microtime()) . random_int(10000, 50000) . "." . $extension;//5、重新命名上传文件名字
                $res           = $images->move($uploadMediaAddr, $newImagesName); //6、使用move方法移动文件.
                return $this->media_addr = $res->getRealPath();
            }
            throw new \Exception(10002);
        }
    }