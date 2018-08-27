<?php
    /**
     * Wechat.php
     *
     * Created by PhpStorm.
     * author: liuml  
     * DateTime: 2018/8/21  11:48
     */

    namespace App\WechatXiaowei\V1\Services;

    use App\WechatXiaowei\V1\Exception\WxException;
    use App\WechatXiaowei\V1\Services\Api\WechatApi;

    class Wechat extends BaseWechat implements WechatApi
    {
        use \App\WechatXiaowei\V1\Services\Traits\Certificate;
        use \App\WechatXiaowei\V1\Services\Traits\UploadMedia;

        /**
         * applyEnter 申请入驻小微商户
         * @return mixed
         */
        public function applyEnter(array $params)
        {
            // 校验参数
            if (!$this->checkParams($params)) {
                throw new WxException(20004);
            }
            // 校验银行卡号前缀是否支持
            if ($this->accountNumberIsSupport($params['account_number'] ?? '')) {
                throw new WxException(20003);
            }

            $data = [
                'version'                => '2.0',
                'cert_sn'                => $this->newResponseData()['serial_no'],
                'mch_id'                 => $this->mch_id,
                'nonce_str'              => $this->getRandChar(),
                'sign_type'              => 'HMAC-SHA256',
                'sign'                   => '',
                'business_code'          => $this->getBusinessCode(),    // 业务申请编号
                'id_card_copy'           => $params['id_card_copy'],    // 身份证人像面照片  media_id
                'id_card_national'       => $params['id_card_national'],    // 身份证国徽面照片
                'id_card_name'           => $this->publicKeyEncrypt($params['id_card_name']),
                'id_card_number'         => $this->publicKeyEncrypt($params['id_card_number']),
                'id_card_valid_time'     => $params['id_card_valid_time'],    // '["1970-01-01","长期"]' string(50)
                'account_name'           => $this->publicKeyEncrypt($params['account_name']),
                'account_bank'           => $params['account_bank'],
                'bank_address_code'      => $params['bank_address_code'],
                'bank_name'              => $params['bank_name'] ?? '',
                'account_number'         => $this->publicKeyEncrypt($params['account_number']),
                'store_name'             => $params['store_name'],
                'store_address_code'     => $params['store_address_code'],
                'store_street'           => $params['store_street'],
                'store_longitude'        => $params['store_longitude'] ?? '',
                'store_latitude'         => $params['store_latitude'] ?? '',
                'store_entrance_pic'     => $params['store_entrance_pic'],
                'indoor_pic'             => $params['indoor_pic'],
                'address_certification'  => $params['address_certification'] ?? '',
                'merchant_shortname'     => $params['merchant_shortname'],
                'service_phone'          => $params['service_phone'],
                'business'               => $params['business'],
                'product_desc'           => $params['product_desc'] ?? '',
                'qualifications'         => $params['qualifications'] ?? '',
                'rate'                   => $params['rate'],
                'business_addition_desc' => $params['business_addition_desc'] ?? '',
                'business_addition_pics' => $params['business_addition_pics'] ?? '',    // ["123","456"] 最多可上传5张照片，请填写已预先上传图片生成好的MediaID
                'contact'                => $this->publicKeyEncrypt($params['contact']),
                'contact_phone'          => $this->publicKeyEncrypt($params['contact_phone']),
                'contact_email'          => isset($params['contact_email']) && !empty($params['contact_email']) ? $this->publicKeyEncrypt($params['contact_email']) : '',
            ];
            // 签名
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'applyment/micro/submit';
            // 数组转xml
            $xml = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res, ['applyment_id'], ['business_code' => $data['business_code']]);
        }

        /**
         * getBusinessCode 生成业务申请编号
         * @return mixed|null|string|string[]
         */
        private function getBusinessCode()
        {
            $millisecond = $this->getMillisecond();
            return mb_strtoupper(md5(uniqid($millisecond . mt_rand())));
        }

        /**
         * accountNumberIsSupport 判断银行卡账号是否支持
         * @param $account_number
         * @return bool
         */
        private function accountNumberIsSupport(string $account_number)
        {
            $account_prefix_6 = substr($account_number, 0, 6);
            $account_prefix_8 = substr($account_number, 0, 8);

            $not_support = ['623501', '621468', '620522', '625191', '622384', '623078', '940034', '622150', '622151', '622181', '622188', '955100', '621095', '620062', '621285', '621798', '621799', '621797', '622199', '621096', '62215049', '62215050', '62215051', '62218849', '62218850', '62218851', '621622', '623219', '621674', '623218', '621599', '623698', '623699', '623686', '621098', '620529', '622180', '622182', '622187', '622189', '621582', '623676', '623677', '622812', '622810', '622811', '628310', '625919', '625368', '625367', '518905', '622835', '625603', '625605', '518905'];
            if (array_search($account_prefix_6, $not_support)) {
                return true;
            }
            if (array_search($account_prefix_8, $not_support)) {
                return true;
            }
            return false;
        }

        /**
         * checkParams 校验入驻接口必填字段信息
         * @param array $params
         * @return bool
         */
        private function checkParams(array $params)
        {
            $data   = ['id_card_copy', 'id_card_national', 'id_card_name', 'id_card_number', 'id_card_valid_time', 'account_name', 'account_bank', 'bank_address_code', 'account_number', 'store_name', 'store_address_code', 'store_street', 'store_entrance_pic', 'indoor_pic', 'merchant_shortname', 'service_phone', 'business', 'contact', 'contact_phone'];
            $result = true;
            foreach ($data as $key => $value) {
                if (!isset($params[$value]) || empty($params[$value])) {
                    $result = false;
                    break;
                }
            }
            return $result;
        }

        /**
         * enquiryOfApplyStatus 入驻申请状态查询
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function enquiryOfApplyStatus(array $params)
        {
            if ((isset($params['applyment_id']) && empty($params['applyment_id'])) && (isset($params['business_code']) && empty($params['business_code']))) {
                throw new WxException(20004);
            }
            $data         = [
                'version'       => '1.0',
                'mch_id'        => $this->mch_id,
                'nonce_str'     => $this->getRandChar(),
                'sign_type'     => 'HMAC-SHA256',
                'sign'          => '',
                'applyment_id'  => $params['applyment_id'] ?? '',
                'business_code' => $params['business_code'] ?? '',
            ];
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'applyment/micro/getstate';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            $rt                 = $this->disposeReturn($res, [
                'applyment_id',
                'applyment_state',
                'applyment_state_desc',
                'sub_mch_id',
                'sign_url',
                'audit_detail',
            ], ['business_code' => $data['business_code']]);
            $rt['audit_detail'] = json_decode($rt['audit_detail'], true);
            return $rt;
        }

        /**
         * tenantConfig  关注配置  小微商户关注功能配置API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function tenantConfig(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            $data         = [
                'mch_id'          => $this->mch_id,
                'sub_mch_id'      => $params['sub_mch_id'],
                'nonce_str'       => $this->getRandChar(),
                'sign_type'       => 'HMAC-SHA256',
                'sign'            => '',
                'sub_appid'       => $params['sub_appid'] ?? 'NULL',
                'subscribe_appid' => $params['subscribe_appid'] ?? '',
                'receipt_appid'   => $params['receipt_appid'] ?? '',
            ];
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'secapi/mkt/addrecommendconf';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res, ['subscribe_appid', 'receipt_appid']);
        }

        /**
         * payTheDirectoryConfig  支付目录配置   小微商户开发配置新增支付目录API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function payTheDirectoryConfig(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            if (!isset($params['jsapi_path'])) {
                throw new WxException(20004, '授权目录必填');
            }
            if (!isset($params['appid'])) {
                throw new WxException(20004, '服务商的公众账号 ID 必填');
            }
            $data         = [
                'appid'      => $params['appid'],
                'mch_id'     => $this->mch_id,
                'sub_mch_id' => $params['sub_mch_id'],
                'jsapi_path' => $params['jsapi_path'],
                'sign'       => '',
            ];
            $data['sign'] = $this->makeSign($data, 'md5');
            $url          = self::WXAPIHOST . 'secapi/mch/addsubdevconfig';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res);
        }

        /**
         * bindAppIdConfig 绑定appid配置  小微商户新增对应APPID关联API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function bindAppIdConfig(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            if (!isset($params['sub_appid'])) {
                throw new WxException(20004, '关联 APPID 必填');
            }
            if (!isset($params['appid'])) {
                throw new WxException(20004, '服务商的公众账号 ID 必填');
            }
            $data         = [
                'appid'      => $params['appid'],
                'mch_id'     => $this->mch_id,
                'sub_mch_id' => $params['sub_mch_id'],
                'sub_appid'  => $params['sub_appid'],
                'sign'       => '',
            ];
            $data['sign'] = $this->makeSign($data, 'md5');
            $url          = self::WXAPIHOST . 'secapi/mch/addsubdevconfig';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res);
        }

        /**
         * inquireConfig 查询配置
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function inquireConfig(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            if (!isset($params['appid'])) {
                throw new WxException(20004, '服务商的公众账号 ID 必填');
            }
            $data         = [
                'mch_id'     => $this->mch_id,
                'sub_mch_id' => $params['sub_mch_id'],
                'sign'       => '',
                'appid'      => $params['appid'],
            ];
            $data['sign'] = $this->makeSign($data, 'md5');
            $url          = self::WXAPIHOST . 'secapi/mch/querysubdevconfig';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            $rt                      = $this->disposeReturn($res, ['jsapi_path_list', 'appid_config_list']);
            $rt['jsapi_path_list']   = json_decode($rt['jsapi_path_list'], true);
            $rt['appid_config_list'] = json_decode($rt['appid_config_list'], true);
            return $rt;
        }

        /**
         * modifyArchives 小微商户修改资料接口-修改结算银行卡
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function modifyArchives(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }

            $data         = [
                'version'           => '1.0',
                'mch_id'            => $this->mch_id,
                'nonce_str'         => $this->getRandChar(),
                'sign_type'         => 'HMAC-SHA256',
                'sub_mch_id'        => $params['sub_mch_id'],
                'sign'              => '',
                'account_number'    => isset($params['account_number']) ? $this->publicKeyEncrypt($params['account_number']) : '',
                'bank_name'         => $params['bank_name'] ?? '',
                'account_bank'      => $params['account_bank'] ?? '',
                'bank_address_code' => $params['bank_address_code'] ?? '',
                'cert_sn'           => $this->newResponseData()['serial_no'],
            ];
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'applyment/micro/modifyarchives';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res, ['sub_mch_id']);
        }

        /**
         * withdrawalState 服务商帮小微商户查询自动提现 - 查询提现状态
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function withdrawalState(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            if (!isset($params['date'])) {
                throw new WxException(20004, '日期必填');
            }

            $data         = [
                'mch_id'     => $this->mch_id,
                'nonce_str'  => $this->getRandChar(),
                'sign_type'  => 'HMAC-SHA256',
                'sub_mch_id' => $params['sub_mch_id'],
                'sign'       => '',
                'date'       => $params['date'],
            ];
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'fund/queryautowithdrawbydate';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            $rt                         = $this->disposeReturn($res, [
                'date',
                'sub_mch_id',
                'withdraw_status',
                'fail_reason',
                'withdraw_id',
                'amount',
                'create_time',
                'success_time',
                'refund_time',
            ]);
            $rt['withdraw_status_desc'] = $this->withdrawStatusDesc($rt['withdraw_status']);
            return $rt;
        }

        /**
         * withdrawStatusMsg 提现状态单据状态字段的中文描述
         * @param $key
         * @return mixed|string
         */
        private function withdrawStatusDesc($key)
        {
            $status = [
                'PROCESSING'           => '提现处理中',
                'SUCCESS'              => '提现操作成功',
                'REFUNDED'             => '银行处理失败，提现操作退票',
                'SUPPORT_RE_WITHDRAW'  => '可重新发起提现',
                'SUPPORT_WITHDRAW'     => '当日无提现单，并且当日净交易额大于0，因此支持发起该日自动提现',
                'NOT_SUPPORT_WITHDRAW' => '当日距今超过30天；或当日无提现单，并且当日净交易额不大于0，因此不支持发起该日自动提现',
                'NO_WITHDRAW_AUTH'     => '商户无提现权限',
            ];
            return $status[$key] ?? '';
        }

        /**
         * reAutoWithdrawByDate 重新发起提现 - 服务商帮小微商户重新发起自动提现
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function reAutoWithdrawByDate(array $params)
        {
            if (!isset($params['sub_mch_id'])) {
                throw new WxException(20004, '小微商户号必填');
            }
            if (!isset($params['date'])) {
                throw new WxException(20004, '日期必填');
            }

            $data         = [
                'mch_id'     => $this->mch_id,
                'nonce_str'  => $this->getRandChar(),
                'sign_type'  => 'HMAC-SHA256',
                'sub_mch_id' => $params['sub_mch_id'],
                'sign'       => '',
                'date'       => $params['date'],
            ];
            $data['sign'] = $this->makeSign($data, $data['sign_type']);
            $url          = self::WXAPIHOST . 'fund/reautowithdrawbydate';
            $xml          = $this->toXml($data);
            // 发起入驻申请请求
            $res = $this->httpsRequest($url, $xml, [], true);
            // 处理返回值
            return $this->disposeReturn($res, [
                'date',
                'sub_mch_id',
                'withdraw_id',
                'amount',
                'create_time',
            ]);
        }

    }