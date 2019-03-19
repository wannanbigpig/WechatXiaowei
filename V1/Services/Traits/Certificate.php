<?php
    /**
     * wechatCertificate.php
     *
     * Created by PhpStorm.
     * author: liuml  <liumenglei0211@163.com>
     * DateTime: 2018/8/23  10:50
     */

    namespace App\WechatXiaowei\V1\Services\Traits;

    use App\WechatXiaowei\V1\Exception\WxException;
    use Illuminate\Support\Facades\Config;

    /**
     * Trait wechatCertificate
     * @package App\WechatXiaowei\V1\Services\wechat\traits
     */
    trait Certificate
    {
        // 私钥
        protected $privateKey;
        // 公钥
        protected $publicKey;
        // 解密后证书地址
        protected $publicKeyAddr;
        // 最新的证书完整响应体存放位置
        protected $newResponseDataAddr;
        // 私钥地址
        protected $privateKeyAddr;

        public function __construct()
        {
            parent::__construct();
            $this->newResponseDataAddr = Config::get('api.wechatConfig.newResponseDataAddr');
            $this->publicKeyAddr       = Config::get('api.wechatConfig.publicKeyAddr');
            $this->privateKeyAddr      = Config::get('api.wechatConfig.privateKeyAddr');
        }

        /**
         * getCertificates  下载平台证书 1.0
         * @return mixed
         */
        public function downloadCertificatesold()
        {
            try {
                $url = self::WXAPIHOST . 'risk/getcertficates';
                // 请求随机串
                $nonce_str = $this->getRandChar();
                // 当前时间戳
                $timestamp = time();
                // 签名串
                $signContent = "GET\n/v3/certificates\n" . $timestamp . "\n" . $nonce_str . "\n\n";
                // 签名值
                $signature = $this->encryptSign($signContent);
                // 含有服务器用于验证商户身份的凭证
                $authorization = 'WECHATPAY2-SHA256-RSA2048 mchid="' . $this->mch_id . '",nonce_str="' . $nonce_str . '",signature="' . $signature . '",timestamp="' . $timestamp . '",serial_no="' . $this->serial_no . '"';
                $curl_v        = curl_version();
                $header        = [
                    'Accept:application/json',
                    // 'Accept-Language:zh-CN',    // 默认 zh-CN 可以不填
                    'Authorization:' . $authorization,
                    'Content-Type:application/json',
                    'User-Agent:curl/' . $curl_v['version'],
                ];
                $result        = $this->httpsRequest($url, NULL, $header);
                // print_r($result);die;
                $responseHeader = $this->parseHeaders($result[2]);
                $http_code      = $result[1];
                $responseBody   = json_decode($result[0], true);
                if ($http_code == 200 && !isset($responseBody['code'])) {
                    return $this->verifySign($responseHeader, $result[0]);
                } else {
                    throw new \Exception($responseBody['code'] . '----' . $responseBody['message']);
                }
            } catch (\Exception $e) {
                throw new WxException($e->getCode(), $e->getMessage());
            }
        }

        /**
         * downloadCertificates 2.0
         * @return mixed
         * @throws WxException
         * @author   liuml  <liumenglei0211@163.com>
         * @DateTime 2019-03-05  11:03
         */
        public function downloadCertificates()
        {
            try {
                $data         = [
                    'mch_id'    => $this->mch_id,
                    'nonce_str' => $this->getRandChar(),
                    'sign_type' => 'HMAC-SHA256',
                    'sign'      => '',
                ];
                $data['sign'] = $this->makeSign($data, $data['sign_type']);
                $url          = self::WXAPIHOST . 'risk/getcertficates';
                $xml          = $this->toXml($data);
                // 发起入驻申请请求
                $result = $this->httpsRequest($url, $xml, [], true);
                // 处理返回值
                $rt = $this->disposeReturn($result, [
                    'mch_id',
                    'nonce_str',
                    'sign',
                    'result_code',
                    'err_code',
                    'err_code_des',
                    'certificates',
                ]);
                if ($rt['result_code'] == 'SUCCESS') {
                    return $this->verifySignold($rt['certificates']);
                } else {
                    throw new \Exception($rt['code'] . '----' . $rt['message']);
                }
            } catch (\Exception $e) {
                throw new WxException($e->getCode(), $e->getMessage());
            }
        }

        /**
         * setHashSign SHA256 with RSA 签名
         * @param $signContent
         * @return string
         */
        protected function encryptSign($signContent)
        {
            // 解析 key 供其他函数使用。
            $privateKey = openssl_get_privatekey($this->getPrivateKey());
            // 调用openssl内置签名方法，生成签名$sign
            openssl_sign($signContent, $sign, $privateKey, "SHA256");
            // 释放内存中私钥资源
            openssl_free_key($privateKey);
            $sign = base64_encode($sign);
            return $sign;
        }

        /**
         * verifyHashSign 校验签名 2.0
         * @param $data
         * @param $signature
         * @return int
         */
        protected function verifySign($responseBody)
        {
            $last_data = $this->newResponseData();
            $new_data  = json_decode($responseBody, true);
            $one       = false;
            if (empty($last_data)) {
                // 没有获取到上一次保存在本地的数据视为第一请求下载证书接口
                $serial_no = $this->getNewCertificates($new_data['data']);
                if($serial_no != ''){
                    return $serial_no;
                }
            } else {
                $serial_no = $last_data['serial_no'];
            }

            $publicKey = $this->getPublicKey();
            if ($publicKey) {
                return $this->getNewCertificates($new_data['data'], $last_data);
            }
            return 0;
        }

        /**
         * verifyHashSign 校验签名 1.0
         * @param $data
         * @param $signature
         * @return int
         */
        protected function verifySignold($responseHeader, $responseBody)
        {
            $last_data = $this->newResponseData();
            $new_data  = json_decode($responseBody, true);

            $one = false;
            if (empty($last_data)) {
                // 没有获取到上一次保存在本地的数据视为第一请求下载证书接口
                $serial_no = $this->getNewCertificates($new_data['data']);
                $one       = true;
            } else {
                $serial_no = $last_data['serial_no'];
            }

            // 注 1：微信支付平台证书序列号位于 HTTP 头`Wechatpay-Serial`，验证签名前请先检查序列号是否跟商户所持有的微信支付平台证书序列号一致。（第一次从 1.1.5.中回包字段 serial_no 获取，非第一次时使用上次本地保存的平台证书序列号）
            if ($serial_no != $responseHeader['Wechatpay-Serial']) {
                if ($one)
                    $this->clearFile();
                return 0;
            }
            $publicKey = $this->getPublicKey();
            if ($publicKey) {
                // 用微信支付平台证书公钥（第一次下载平台证书时从 1.1.5.中 “加密后的证书内容”进行解密获得。非第一次时使用上次本地保存的公钥）对“签名串”进行 SHA256 with RSA 签名验证
                $data              = $this->signatureValidation($responseHeader, $responseBody);
                $signature         = base64_decode($responseHeader['Wechatpay-Signature']);
                $publicKeyResource = openssl_get_publickey($publicKey);
                $f                 = openssl_verify($data, $signature, $publicKeyResource, "SHA256");
                openssl_free_key($publicKeyResource);
                if ($f == 1) {
                    // 获取弃用日期最长证书
                    return $this->getNewCertificates($new_data['data'], $last_data);
                }
                return $f;
            }
            return 0;
        }

        /**
         * signatureValidation 拼装校验签名串
         * @param $responseHeader
         * @param $responseBody
         * @return mixed
         */
        protected function signatureValidation($responseHeader, $responseBody)
        {
            return $responseHeader['Wechatpay-Timestamp'] . "\n" . $responseHeader['Wechatpay-Nonce'] . "\n" . $responseBody . "\n";
        }

        /**
         * getNewCertificates  获取弃用日期最长证书
         * @param array $data
         * @return false|int|string
         */
        protected function getNewCertificates(array $data, $last_data = '')
        {
            $key = 0;
            if (count($data) > 1) {
                $timeArr = [];
                foreach ($data as $k => $v) {
                    $timeArr[$k] = strtotime($v['expire_time']);
                }
                $key = array_search(max($timeArr), $timeArr);
            }
            if (empty($last_data)) {
                $this->decryptCiphertext($data[$key]);
            } else {
                if (strtotime($last_data['expire_time']) < strtotime($data[$key]['expire_time'])) {
                    $this->decryptCiphertext($data[$key]);
                } else {
                    return $last_data['serial_no'];
                }
            }
            return $data[$key]['serial_no'];
        }

        /**
         * decryptCiphertext  AEAD_AES_256_GCM 解密加密后的证书内容得到平台证书的明文
         * @param $ciphertext
         * @param $ad
         * @param $nonce
         * @return string
         */
        protected function decryptCiphertext($data)
        {
            $encryptCertificate = $data['encrypt_certificate'];
            $ciphertext         = base64_decode($encryptCertificate['ciphertext']);
            $associated_data    = $encryptCertificate['associated_data'];
            $nonce              = $encryptCertificate['nonce'];
            // sodium_crypto_aead_aes256gcm_decrypt >=7.2版本，去php.ini里面开启下libsodium扩展就可以，之前版本需要安装libsodium扩展，具体查看php.net（ps.使用这个函数对扩展的版本也有要求哦，扩展版本 >=1.08）
            $plaintext = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $this->aes_key);
            $this->savePublicKey($plaintext);
            $this->newResponseData($data);
            return true;
        }

        /**
         * getPrivateKey 超级管理员登录商户平台，在“账户中心”->“API 安全”->”API 证书（权威 CA 颁发）”中申请
         * API 商户证书，申请过程中会获取到私钥证书文件（申请流程详见 1.1.3.3“申请 API 商户证书“），打开
         * 文件获取私钥字符（定义变量 string sKey）
         * @return string
         */
        protected function getPrivateKey()
        {
            if (file_exists($this->privateKeyAddr))
                return $this->privateKey ? : $this->privateKey = file_get_contents($this->privateKeyAddr);
        }

        /**
         * savePublicKey 保存解密后的明文
         * @param $plaintext
         */
        protected function savePublicKey($plaintext)
        {
            $this->publicKey = $plaintext;
            file_put_contents($this->publicKeyAddr, $plaintext);
            return $plaintext;
        }

        /**
         * getPublicKey 获取上一次本地保存的公钥
         * @return bool|string
         */
        protected function getPublicKey()
        {
            if (file_exists($this->publicKeyAddr))
                return $this->publicKey ? : $this->publicKey = file_get_contents($this->publicKeyAddr);
            return '';
        }

        /**
         * newResponseData 下载证书接口返回数据对比后最新的一次响应数据
         * @param $key
         * @param $data
         */
        protected function newResponseData(array $data = [])
        {
            if (!empty($data)) {
                $json = json_encode($data, JSON_UNESCAPED_UNICODE);
                if (file_put_contents($this->newResponseDataAddr, $json)) {
                    return true;
                }
                return false;
            }

            if (file_exists($this->newResponseDataAddr))
                return json_decode(file_get_contents($this->newResponseDataAddr), true);
            return [];
        }


        /**
         * clearFile 删除文件不需要的缓存文件
         * @param $str
         * @return bool
         */
        protected function clearFile()
        {
            unlink($this->newResponseDataAddr);
            unlink($this->publicKeyAddr);
        }

        /**
         * publicKeyEncrypt 对身份证等敏感信息加密
         * @param string $string
         * @return string
         * @throws WxException
         */
        protected function publicKeyEncrypt(string $string)
        {
            $crypted   = '';
            $publicKey = $this->getPublicKey();
            if ($publicKey) {
                $publicKeyResource = openssl_get_publickey($publicKey);
                $f                 = openssl_public_encrypt($string, $crypted, $publicKeyResource, OPENSSL_PKCS1_PADDING);
                openssl_free_key($publicKeyResource);
                if ($f) {
                    return base64_encode($crypted);
                }
            }
            throw new WxException(20002);
        }

        /**
         * getRandChar 获取随机字符串
         * @param int $length
         * @return mixed
         */
        abstract protected function getRandChar($length = 32);

        /**
         * httpsRequest https请求
         * @param        $url
         * @param string $data
         * @param array  $headers
         * @return mixed
         */
        abstract protected function httpsRequest($url, $data = '', array $headers = [], $userCert = false, $timeout = 30);

        /**
         * parseHeaders 解析curl获取到response header
         * @param $header
         * @return mixed
         */
        abstract protected function parseHeaders($header);
    }
