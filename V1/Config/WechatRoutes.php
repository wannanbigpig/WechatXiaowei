<?php
    $api->group(['namespace' => 'App\Wechat\V1\Controllers', 'prefix' => 'wechat', 'middleware' => ['Cors']], function($api) {
        //TestController
        $api->any('index', 'TestController@index');
        $api->any('error/geterrorcode', 'ArgumentsController@getErrorCode');
        $api->any('arguments/geterrorcode', 'ArgumentsController@getRateList');
        // WechatController
        $api->any('certificates', 'WechatController@getCertificates');
        $api->post('upload', 'WechatController@uploadMedia');    // 图片上传
        $api->post('applyenter', 'WechatController@applyEnter');    // 申请入驻
        $api->post('applystatus', 'WechatController@enquiryOfApplyStatus');    // 入驻状态查询
        $api->post('tenantconfig', 'WechatController@tenantConfig');    // 小微商户关注功能配置API
        $api->post('addsubdevconfig', 'WechatController@addSubDevConfig');    // 小微商户开发配置新增支付目录API
        $api->post('bindappidconfig', 'WechatController@bindAppIdConfig');    // 小微商户开发配置新增支付目录API
        $api->post('inquireconfig', 'WechatController@inquireConfig');    // 查询配置
        $api->post('modifyarchives', 'WechatController@modifyArchives');    // 修改银行卡号
        $api->post('withdrawalstate', 'WechatController@withdrawalState');    // 修改银行卡号
        $api->post('reautowithdrawbydate', 'WechatController@reAutoWithdrawByDate');    // 修改银行卡号
    });