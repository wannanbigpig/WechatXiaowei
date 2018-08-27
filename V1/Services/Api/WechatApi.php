<?php
    /**
     * WechatApi.php
     *
     * Created by PhpStorm.
     * author: liuml  
     * DateTime: 2018/8/21  11:55
     */

    namespace App\WechatXiaowei\V1\Services\Api;


    interface wechatApi
    {
        /**
         * applyEnter 申请入驻小微商户
         * @return mixed
         */
        public function applyEnter(array $params);

        /**
         * enquiryOfApplyStatus 入驻申请状态查询
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function enquiryOfApplyStatus(array $params);

        /**
         * tenantConfig  关注配置  小微商户关注功能配置API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function tenantConfig(array $params);

        /**
         * payTheDirectoryConfig  支付目录配置   小微商户开发配置新增支付目录API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function payTheDirectoryConfig(array $params);

        /**
         * bindAppIdConfig 绑定appid配置  小微商户新增对应APPID关联API
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function bindAppIdConfig(array $params);

        /**
         * inquireConfig 查询配置
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function inquireConfig(array $params);

        /**
         * modifyArchives 小微商户修改资料接口-修改结算银行卡
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function modifyArchives(array $params);

        /**
         * withdrawalState 服务商帮小微商户查询自动提现 - 查询提现状态
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function withdrawalState(array $params);

        /**
         * reAutoWithdrawByDate 重新发起提现 - 服务商帮小微商户重新发起自动提现
         * @param array $params
         * @return array
         * @throws WxException
         */
        public function reAutoWithdrawByDate(array $params);
    }