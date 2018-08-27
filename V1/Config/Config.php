<?php
    /**
     * Config.php
     *
     * Created by PhpStorm.
     * author: liuml  <liumenglei0211@163.com>
     * DateTime: 2018/8/21  21:26
     */

    return [
        'mch_id'              => '*******',    // 服务商商户号
        'serial_no'           => '*****************',    // 商户证书序列号
        'aes_key'             => '*******',    // 商户证书序列号
        'diy_key'             => '***************',    // 商户自定义key
        'privateKeyAddr'      => app_path() . '/Wechat/V1/Certificate/*********_key.pem',    // 私钥证书位置
        'sslCertAddr'         => app_path() . '/Wechat/V1/Certificate/*********_cert.pem',    // 证书存放位置
        'publicKeyAddr'       => app_path() . '/Wechat/V1/Certificate/****.pem',    // 包含公钥的解密后明文证书存放位置
        'newResponseDataAddr' => app_path() . '/Wechat/V1/Certificate/******.json',    // 最新的证书完整响应体存放位置
        'uploadMediaAddr'     => app_path() . '/Wechat/V1/Upload/',    // 身份证图片等上传临时保存路径
    ];