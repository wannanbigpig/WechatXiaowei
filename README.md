# <font style="color:red">最新公告</font>
* <font style="color:red">此处不在更新</font>

* <font style="color:red">小微商户最新的已经合并到了EasyWeChat中，安装使用请点这 [overtrue/wechat](https://github.com/overtrue/wechat)</font>
* <font style="color:red">composer包使用有问题，你可以在这里提问 [overtrue/wechat：Issues](https://github.com/overtrue/wechat/issues),小微商户模块的问题请@wannanbigpig</font>
# WechatXiaowei
微信小微商户接口PHP示例

## 使用说明

1、该接口使用laravel框架写的，如果移植到其他框架需要改动里面获取配置等方法、图片上传接口需要改动，其他地方基本没有使用laravel内置函数，具体的可以根据调试信息修改下，调整地方不多。（注：PHP版本小于7的话，里面 ?? 写法需要改掉）

2、下载证书解密返回的密文需要开启libsodium扩展（PHP >= 7.2 安装包自带这个扩展，去php.ini开启一下就行，< 7.2 的需要去安装这个扩展）【安装方法：https://blog.csdn.net/u010324331/article/details/82153067 】

3、调用申请入驻等接口里面需要下载证书接口返回的序列号和需要解密后证书 public_key 来加密敏感信息，所以需要先调用下载证书接口

4、该示例代码仅供学习使用，未经充分测试。直接使用到生产环境中导致的后果概不负责。

## 2019年3月5号更新

1、更新了下载证书接口

2、新增小微商户升级接口

3、新增小微商户升级状态查询接口

4、其他
