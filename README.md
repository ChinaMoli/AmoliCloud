<p align="center">
<img src="https://s2.ax1x.com/2019/05/30/VKshgs.png" alt="AmoliCloud">
</p>
<h1 align="center">AmoliCloud</h1>

> 😊一款轻量级的私有云存储系统，支持本地、阿里云OSS、腾讯云COS存储，提供简单的文件列表、上传下载、管理等功能。

[![npm](https://img.shields.io/npm/l/dplayer.svg?style=flat-square)](https://github.com/ChinaMoli/AmoliCloud/blob/master/LICENSE)
[![Travis](https://img.shields.io/travis/MoePlayer/DPlayer.svg?style=flat-square)](https://travis-ci.org/ChinaMoli/AmoliCloud)

### 预览/Demo
-----
*  <https://demo.amoli.co>
![](https://s2.ax1x.com/2019/06/09/VDj48f.jpg)

### 注意事项/Attention
-----
#### 环境
**运行环境**：PHP 5.6+ 推荐7.x，cURL()支持

**开发环境**：PHP 7.0、Nginx、Windows

#### 兼容性
支持IE9及以上的现代浏览器，并且已在 Chrome、firefox、IE11 等浏览器测试使用正常

#### 版权
本程序由 无名氏Studio(https://wums.cn) 开发，您可以随意修改、使用、转载。使用或转载时请务必保留出处，保留版权是对作者最大的尊重！

### 部署/Build
-----
#### 下载：
Releases：<https://github.com/ChinaMoli/AmoliCloud/releases>，或使用git：
~~~
git clone https://github.com/ChinaMoli/AmoliCloud.git
~~~

#### 更新：
Releases：<https://github.com/ChinaMoli/AmoliCloud/releases>，或使用git
~~~
git clone https://github.com/ChinaMoli/AmoliCloud.git
~~~

### 帮助/Help
-----
* **详细搭建教程**：<https://wums.cn/archives/AmoliCloud-install.html>
* **OSS配置教程**：<https://wums.cn/archives/AMoliCloud-deploy.html>
* **COS配置教程**：<https://wums.cn/archives/AmoliCloud-CosConfig.html>

### 更新日志/ChangeLog
-----
#### version 4.2.0 `2019-06-15`
* [新增] 腾讯云COS对象存储
* [新增] OSS单独选择文件夹列出(默认为根目录)
* [新增] 后台设置前台统计代码
* [新增] 未知Bug无数
* [优化] 上传成功后自动刷新当前目录
* [优化] 多处细节，提升访问速度
* [优化] 提升程序安全，加入多处入口文件
* [修复] 本地存储上传文件卡顿，大文件出错
* [修复] IIS环境下，后台加载出错

更多：[CHANGELOG.md](https://github.com/ChinaMoli/AmoliCloud/blob/master/CHANGELOG.md)

### 更新计划/Preview
-----
* 增加七牛云、又拍云等主流对象存储

### 开源协议/License
-----
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
