<?php
    /**
     * uploadMedia.php
     *
     * Created by PhpStorm.
     * author: liuml  <liumenglei0211@163.com>
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

        protected $media;

        protected $media_hash;

        /**
         * uploadImg 图片上传
         * @return array
         * @throws WxException
         */
        public function uploadImg()
        {
            $url = self::WXAPIHOST . 'secapi/mch/uploadmedia';
            // 判断图片地址是否为空，空的话就调用图片上传方法，把图片上传到服务器
            empty($this->media_addr) && $media = $this->saveImg();
            $arr = [];
            if ($media == 2 && $this->media) {
                $rt = [
                    'media_id' => $this->media->media_id,
                ];
                if ($this->media->uid != $this->uid) {
                    $arr = [
                        'uid'        => $this->uid,
                        'created_at' => time(),
                        'md5_value'  => $this->media->md5_value,
                        'media_addr' => $this->media_addr,
                    ];
                }
            } else {
                // 判断图片是否存在
                if (!file_exists($this->media_addr))
                    throw new WxException(10001);
                $data              = [
                    'mch_id'     => $this->mch_id,
                    'media_hash' => $this->hashMedia($this->media_addr),
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
                $rt  = $this->disposeReturn($res, ['media_id']);
                $arr = [
                    'uid'        => $this->uid,
                    'created_at' => time(),
                    'md5_value'  => $data['media_hash'],
                    'media_addr' => $this->media_addr,
                ];
            }
            if (!empty($arr)) {
                \DB::table('xw_media')->insert(
                    array_merge($arr, $rt)
                );
            }
            return $rt;
        }

        /**
         * saveImg 上传图片到服务器
         * @return string
         * @throws WxException
         */
        protected function saveImg()
        {
            $images = Request::file('media'); //1、使用laravel 自带的request类来获取一下文件
            if (!$images) {
                return $this->get_base64_img(\Request::post('media'));
                // throw new WxException(0, '至少上传一张图片');
            }
            $media = $this->mediaIsExist($images);
            if ($media !== false) {
                return 2;
            }
            $uploadMediaAddr = Config::get('api.wechatConfig.uploadMediaAddr'); //2、定义图片上传路径
            // $imagesName      = $images->getClientOriginalName(); //3、获取上传图片的文件名
            $extension = $images->getClientOriginalExtension(); //4、获取上传图片的后缀名
            if (in_array($extension, ['jpeg', 'jpg', 'bmp', 'png'])) {
                $newImagesName = md5(microtime()) . random_int(10000, 50000) . "." . $extension;//5、重新命名上传文件名字
                $res           = $images->move($uploadMediaAddr, $newImagesName); //6、使用move方法移动文件.
                $this->setMediaAddr($res->getRealPath());
                return 1;
            }
            throw new WXException(10002);
        }

        /**
         * get_base64_img base64图片上传
         * @param $base64
         * @param string $path
         * @return bool|string
         */
        protected function get_base64_img($base64)
        {
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
                $path = Config::get('api.wechatConfig.uploadMediaAddr'); // 图片上传路径
                $type = $result[2];
                if (in_array($type, ['jpeg', 'jpg', 'bmp', 'png'])) {
                    $new_file = $path . md5(microtime()) . random_int(10000, 50000) . "." . $type;//5、重新命名上传文件名字;
                    if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))) {
                        $this->setMediaAddr($new_file);
                        return 1;
                    }
                    throw new WXException(10001);
                } else {
                    throw new WXException(10002);
                }
            }
            throw new WxException(0, '至少上传一张图片');
        }

        /**
         * setMediaAddr 设置图片地址
         * @param $media
         */
        protected function setMediaAddr($media_addr)
        {
            $this->media_addr = $media_addr;
        }

        /**
         * setMedia 设置历史已上传相同图片的信息
         * @param $media
         */
        protected function setMedia($media)
        {
            $this->media = $media;
        }

        /**
         * hashMedia 设置上传图片hash值
         * @param $media_addr
         * @param string $type
         * @return string
         */
        protected function hashMedia($media_addr, $type = 'md5')
        {
            return $this->media_hash ?? hash_file($type, $media_addr);
        }

        /**
         * mediaIsExist 通过图片md5值查找图片是否上传过.
         * @param $images
         * @return bool
         */
        protected function mediaIsExist($images)
        {
            $media = \DB::table('xw_media')->where('md5_value', '=', $this->hashMedia($images))->first();
            if ($media) {
                $this->setMediaAddr($media->media_addr);
                $this->setMedia($media);
                return true;
            }
            return false;
        }
    }