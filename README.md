# wxauth-server
通过认证的公众号实现网页扫描登陆(服务端)

此为Yii2模块, 需要运行在Yii2框架下

## 使用场景
1. 有认证过的微信公众号
2. 没有开发者账号(正常使用微信扫描登陆要申请开发者账号, 300认证费用, 每个网站都要提交资料, 审核, 很麻烦)
3. WEB端希望通过扫描登陆

## 安装使用
1、 通过composer安装: `composer require raysoft/wxauth-server`

2、 Yii2项目根目录运行脚本创建数据表: `php yii migrate --migrationPath=vendor/raysoft/wxauth-server/migrations`

3、 配置模块:
```
'modules' => [
    ...
    'wxauth' => [ // 配置模块名, 客户端连接服务端需要用到这个
        'class' => 'raysoft\WxAuthServer\Module'
    ]
    ...
  ]
```
4、 在wxlogin_app表中添加一条记录, 对应一个客户端, 以下为必填项:

```
name: 客户端名, 显示在用户授权界面
key: key名, 客户端以此确定身份
secret: 密钥, 用于签名
```