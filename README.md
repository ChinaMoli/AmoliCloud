<p align="center">
<img src="https://github.com/ChinaMoli/AmoliCloud/blob/master/admin/images/Amoli.png" alt="AmoliCloud">
</p>
<h1 align="center">AmoliCloud</h1>
> 😊 

[![npm](https://img.shields.io/npm/l/dplayer.svg?style=flat-square)](https://github.com/ChinaMoli/AmoliCloud/blob/master/LICENSE)
本项目于hai5和oneindex类似，不占用服务器空间，不走服务器流量，直接列出 阿里云OSS 目录和文件。

在具备PHP代码执行环境和OSS对象存储服务的条件下，作为云HTTP文件服务器，提供简单的文件列表、上传下载、管理等功能。

相较于将文件直接存放在本地，OSS则无存储总量限制、传输速率限制、低可靠数据安全等问题，对于移动终端也有良好的支持性。

用户评价：Amoli私有云是私有云存储领域优秀的开源PHP建站系统，功能强大，深不可测。

## 预览/Demo
*  <https://new.amoli.co>
* ![](https://i.loli.net/2019/05/26/5ce9b3ceca63145762.png)

## 部署/Build
* 环境：
PHP >= 5.6 推荐7.x，cURL()支持
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
version 4.0.1 2019-05-26

    [新增] 前端验证开关(前端密码留空即为不开启)
```
更多：[CHANGELOG.md](https://github.com/ChinaMoli/AmoliCloud/blob/master/CHANGELOG.md)

## 后续可能的改动/Preview
```
增加本地存储
```

## 开源协议/License
```
MIT License

Copyright (c) 2019 ChinaMoli

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
