<?php
    /**
     * BaseWechatController.php
     *
     * Created by PhpStorm.
     * author: liuml  <liumenglei0211@163.com>
     * DateTime: 2018/8/21  11:25
     */

    namespace App\WechatXiaowei\V1\Controllers;

    use App\WechatXiaowei\V1\Exception\WxException;
    use App\WechatXiaowei\V1\Services\Wechat;

    class WechatController extends BaseController
    {
        public static $wechat;

        public function __construct()
        {
            static::$wechat = new Wechat();
        }

        /**
         * getCertificates  1、下载证书 微信官方推荐定时执行，间隔应小于12小时
         * @return array
         * @throws \App\Wechat\V1\Exception\WxException
         */
        public function getCertificates()
        {
            try {
                $res = static::$wechat->downloadCertificates();
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * uploadMedia 2、上传图片
         * @return array
         * @throws \App\Wechat\V1\Exception\WxException
         */
        public function uploadMedia()
        {
            try {
                $res = static::$wechat->uploadImg();
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * applyEnter 3、申请入驻
         * @param \Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function applyEnter()
        {
            try {
                $res = static::$wechat->applyEnter(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * enquiryOfApplyStatus 4、查询入驻状态
         * @return \Illuminate\Http\JsonResponse
         */
        public function enquiryOfApplyStatus()
        {
            try {
                $res = static::$wechat->enquiryOfApplyStatus(\Request::all());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * tenantConfig 5、关注配置  小微商户关注功能配置API
         * @return \Illuminate\Http\JsonResponse
         */
        public function tenantConfig()
        {
            try {
                $res = static::$wechat->tenantConfig(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * payTheDirectoryConfig 6、支付目录配置   小微商户开发配置新增支付目录API
         * @return \Illuminate\Http\JsonResponse
         */
        public function addSubDevConfig()
        {
            try {
                $res = static::$wechat->payTheDirectoryConfig(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * bindAppIdConfig 7、绑定appid配置  小微商户新增对应APPID关联API
         * @return \Illuminate\Http\JsonResponse
         */
        public function bindAppIdConfig()
        {
            try {
                $res = static::$wechat->bindAppIdConfig(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * inquireConfig 8、查询配置
         * @return \Illuminate\Http\JsonResponse
         */
        public function inquireConfig()
        {
            try {
                $res = static::$wechat->inquireConfig(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * modifyArchives  9、小微商户修改资料接口-修改结算银行卡
         * @return \Illuminate\Http\JsonResponse
         */
        public function modifyArchives()
        {
            try {
                $res = static::$wechat->modifyArchives(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * withdrawalState 10、服务商帮小微商户查询自动提现 - 查询提现状态
         * @return \Illuminate\Http\JsonResponse
         */
        public function withdrawalState()
        {
            try {
                $res = static::$wechat->withdrawalState(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * reAutoWithdrawByDate 重新发起提现 - 服务商帮小微商户重新发起自动提现
         * @return \Illuminate\Http\JsonResponse
         */
        public function reAutoWithdrawByDate()
        {
            try {
                $res = static::$wechat->reAutoWithdrawByDate(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * getApplyEnterList 获取入驻列表
         * @return \Illuminate\Http\JsonResponse
         * @author   liuml  <liumenglei0211@163.com>
         * @DateTime 2018/9/19  15:20
         */
        public function getApplyEnterList(){
            try {
                $res = static::$wechat->getApplyEnterList(\Request::all());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * getApplyEnterInfo 获取入驻详情
         * @return \Illuminate\Http\JsonResponse
         * @author   liuml  <liumenglei0211@163.com>
         * @DateTime 2018/9/19  15:22
         */
        public function getApplyEnterInfo(){
            try {
                $res = static::$wechat->getApplyEnterInfo(\Request::get('apply_id'));
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * submitUpGrade 商户升级接口
         * @return \Illuminate\Http\JsonResponse
         * @author   liuml  <liumenglei0211@163.com>
         * @DateTime 2019-03-04  12:01
         */
        public function submitUpGrade()
        {
            try {
                $res = static::$wechat->submitUpGrade(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }

        /**
         * upGradeIsThrough 商户升级状态查询
         * @return \Illuminate\Http\JsonResponse
         * @author   liuml  <liumenglei0211@163.com>
         * @DateTime 2019-03-04  12:02
         */
        public function upGradeIsThrough()
        {
            try {
                $res = static::$wechat->upGradeIsThrough(\Request::post());
                return static::returnData(1, '操作成功', $res);
            } catch (WxException $e) {
                return static::returnData($e->getCode(), $e->getMessage());
            } catch (\Exception $e) {
                return static::returnData(-1, '服务器错误', '', 500);
            }
        }


    }