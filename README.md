# AmoliCloud
本项目于hai5和oneindex类似，不占用服务器空间，不走服务器流量，直接列出 阿里云OSS 目录和文件。

在具备PHP代码执行环境和OSS对象存储服务的条件下，作为云HTTP文件服务器，提供简单的文件列表、上传下载、管理等功能。

相较于将文件直接存放在本地，OSS则无存储总量限制、传输速率限制、低可靠数据安全等问题，对于移动终端也有良好的支持性。

用户评价：Amoli私有云是私有云存储领域优秀的开源PHP建站系统，功能强大，深不可测。

## 预览/Demo
*  <https://new.amoli.co>
* ![](https://i.loli.net/2019/05/26/5ce9b3ceca63145762.png)

## 部署/Build
* 环境：
PHP >= 5.6，cURL()支持
* 下载：
Releases：<https://github.com/ChinaMoli/AmoliCloud/releases>，或使用git：
~~~
# git clone https://github.com/ChinaMoli/AmoliCloud.git
~~~
* 更新：
Releases：<https://github.com/ChinaMoli/AmoliCloud/releases>，或使用git
* 配置OSS服务：
1. 开通OSS服务、新建存储空间、上传文件：[OSS新手入门](https://promotion.aliyun.com/ntms/ossedu2.html)
2. 了解基本的OSS属性信息，得到Endpoint
3. 申请具有对应访问权限的AccessKey
4. 详细获取教程&OSS配置<https://wums.cn/archives/AMoliCloud-deploy.html>

## 更新日志/ChangeLog
```
version 4.0.0 2019-05-21
    [项目] 代码完全重构
    [项目] 前端采用Bootstrap4和layer构建
    [项目] 后端采用开源项目layuicms构建
    [新增] 支持OSS自定义域名
    [新增] 前端密码验证，正确输入密码后才可进入
    [新增] 前端支持部分文件在线预览
    [新增] 前端根据文件后缀名显示不同图标
    [优化] 安装步骤，安装成功率
    [优化] 前端全部使用Ajax异步请求提升用户访问
    [优化] 自动刷新文件不再使用手动
    [优化] 后台更改为响应式，手机端也可以操作
    [修复] 文件URL带特殊符号时下载不正常
```

## 后续可能的改动/Preview
```
增加本地存储
增加首页验证开关
```

## 开源协议/License
[GNU General Public License v3.0](https://github.com/ChinaMoli/AmoliCloud/blob/master/LICENSE)