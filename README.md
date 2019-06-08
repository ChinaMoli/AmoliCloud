<p align="center">
<img src="https://s2.ax1x.com/2019/05/30/VKshgs.png" alt="AmoliCloud">
</p>
<h1 align="center">AmoliCloud</h1>

> 😊私有云存储系统，支持本地存储以及阿里云OSS，提供简单的文件列表、上传下载、管理等功能。

[![npm](https://img.shields.io/npm/l/dplayer.svg?style=flat-square)](https://github.com/ChinaMoli/AmoliCloud/blob/master/LICENSE)
[![Travis](https://img.shields.io/travis/MoePlayer/DPlayer.svg?style=flat-square)](https://travis-ci.org/ChinaMoli/AmoliCloud)

## 预览/Demo
*  <https://demo.amoli.co>
* ![](https://s2.ax1x.com/2019/06/09/VDj48f.jpg)

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
~~~
# git clone https://github.com/ChinaMoli/AmoliCloud.git
~~~
* 配置OSS服务：
1. 开通OSS服务、新建存储空间、上传文件：[OSS新手入门](https://promotion.aliyun.com/ntms/ossedu2.html)
2. 了解基本的OSS属性信息，得到Endpoint
3. 申请具有对应访问权限的AccessKey
4. 详细获取教程&OSS配置<https://wums.cn/archives/AMoliCloud-deploy.html>

## 更新日志/ChangeLog
```
version 4.1.0 2019-06-09

    [新增] 自动检测更新，提示更新
    [新增] 本地存储，没有OSS也可以使用
    [新增] 首页验证开关(留空即为关闭)
    [新增] 首页后台系统基本参数
    [新增] 未知Bug无数
    [优化] 自动提示安装，避免误操作
    [优化] 增加多处备注，避免误操作
    [优化] 将常用功能放在后台首页，方便使用
    [优化] 登录步骤，加入简单的验证机制
    [优化] OSS设置和网站设置合并为一个页面
    [优化] 多处细节，提升访问速度
    [修复] 修复后台已知Bug
```
更多：[CHANGELOG.md](https://github.com/ChinaMoli/AmoliCloud/blob/master/CHANGELOG.md)

## 后续可能的改动/Preview
```
增加七牛云，腾讯云，又拍云的主流存储
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
