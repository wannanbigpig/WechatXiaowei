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
        $api->any('applystatus', 'WechatController@enquiryOfApplyStatus');    // 入驻状态查询
        $api->post('tenantconfig', 'WechatController@tenantConfig');    // 小微商户关注功能配置API
        $api->post('addsubdevconfig', 'WechatController@addSubDevConfig');    // 小微商户开发配置新增支付目录API
        $api->post('bindappidconfig', 'WechatController@bindAppIdConfig');    // 小微商户开发配置新增支付目录API
        $api->post('inquireconfig', 'WechatController@inquireConfig');    // 查询配置
        $api->post('modifyarchives', 'WechatController@modifyArchives');    // 修改银行卡号
        $api->post('withdrawalstate', 'WechatController@withdrawalState');    // 修改银行卡号
        $api->post('reautowithdrawbydate', 'WechatController@reAutoWithdrawByDate');    // 修改银行卡号
        $api->post('submitupgrade', 'WechatController@submitUpGrade');    // 商户升级接口
        $api->post('upgsradeisthrough', 'WechatController@upGradeIsThrough');    // 商户升级状态查询


        $api->get('getapplyenterlist', 'WechatController@getApplyEnterList');    // 获取申请入住列表
        $api->get('getapplyenterinfo', 'WechatController@getApplyEnterInfo');    // 获取申请入住信息详情
    });

    $api->get('showimg/{media_id}', function($media_id) {
        ob_clean();
        ob_start();
        $media = DB::table('xw_media')->where('media_id', '=', $media_id)->first();
        if ($media && file_exists($media->media_addr)) {
            $img = file_get_contents($media->media_addr);
        } else {
            die('暂无展示图片');
        }
        echo $img;
        $content = ob_get_clean();
        return response($content, 200, [
            'Content-Type' => 'image/png',
        ]);
    });