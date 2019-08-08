var nowHash, verify;
// 网页初始化
$(function () {
    var version = '4.2.2';
    $.ajax({
        url: 'Ajax.php?act=getConfig',
        dataType: 'json',
        success: function (data) {
            var item = data.data;
            verify = item.verify;
            // 检测是否安装
            if (!item.install) {
                layer.open({
                    title: '提示',
                    content: '你还没有安装程序，点击确定安装',
                    icon: 2,
                    yes: function () { window.location.href = 'install/index.php'; }
                })
                return;
            }
            // 保留版权是对作者最大的尊重。
            console.info('欢迎使用 AmoliCloud!\n当前版本：' + version + ' \n作者：无名氏Studio(https://wums.cn)\n官网：Amoli私有云(https://www.amoli.co)\nGithub：https://github.com/ChinaMoli/AmoliCloud');
            $('title,.navbar-brand').prepend(item.name);// 配置前端信息
            $('#record').text(item.record);
            if (item.log) {
                doHash();
            } else {
                layer.open({
                    type: 1,
                    title: '请输入查看密码',
                    area: ['350px', 'auto'],
                    content: '<div class="container text-right"><br><form onsubmit="return login();"><div class="input-group mb-3"><div class="input-group-prepend"><span class="input-group-text">密码：</span></div><input type="password" class="form-control" id="indexpass"></div><input type="submit" class="btn btn-primary" value="确认"><div><p></p></div></form></div>'
                });
            }
        },
        error: function () {
            layer.open({
                title: '提示',
                content: '请检查当前服务器的PHP版本<br>PHP版本必须高于5.6！',
                icon: 2,
            })
        }
    })
})
// 输出文件图标
function getType(type) {
    var result = "";
    switch (type) {
        case "zip": case "rar": case "7z":
            result = "file_zip";
            break;
        case "jpg": case "png": case "bmp": case "gif": case "ico":
            result = "file_img";
            break;
        case "htm": case "html":
            result = "file_html";
            break;
        case "php": case "css": case "jsp": case "js":
            result = "file_code";
            break;
        case "exe":
            result = "file_exe";
            break;
        case "docx": case "doc":
            result = "file_word";
            break;
        case "xlsx": case "xls":
            result = "file_excel";
            break;
        case "pptx": case "ppt":
            result = "file_ppt";
            break;
        case "pdf":
            result = "file_pdf";
            break;
        case "psd":
            result = "file_psd";
            break;
        case "mp4":
            result = "file_video";
            break;
        case "mp3":
            result = "file_music";
            break;
        case "txt":
            result = "file_txt";
            break;
        case "wjj":
            result = "folder";
            break;
        case "apk":
            result = "file_apk";
            break;
        default:
            result = "file";
    }
    return result;
}
// 获取文件和目录
function getList(type, ListNav, ojb) {
    layer.load(0, { shade: false });
    var dir = '';
    switch (type) {
        case 'nav': case 'hash':
            dir = ListNav;
            break;
        case 'ml':
            dir = ListNav + $(ojb).text() + '/';
            break;
        default:
            var nav = '<a class="breadcrumb-item"></a>'
            $('#nav').html(nav);
    }
    $.ajax({
        url: 'Ajax.php?act=getList',
        type: 'POST',
        data: { 'dir': dir },
        dataType: 'json',
        error: function () {
            $('#list').html('<th class="text-center" colspan="4">请求错误</th>');
            layer.closeAll('loading');
        },
        success: function (data) {
            setHash(dir);// 设置hash
            var str,
                item = data.data;
            for (var i = 0, Max = item.length; i < Max; i++) {
                if (item[i].type == 'wjj') {
                    var name = '<a href="javascript:;" onclick="getList(\'ml\',\'' + dir + '\',this)">' + item[i].name + '</a>';
                } else {
                    var name = '<a href="javascript:;" onclick="downVerify(\'' + item[i].type + '\',\'' + item[i].name + '\', \'' + dir + item[i].name + '\')">' + item[i].name + '</a>';
                }
                str += '<tr><td><svg class="icon" aria-hidden="true"><use xlink:href="#icon-' + getType(item[i].type) + '"></use></svg></td><td>' + name + '</td><td class ="text-right">' + item[i].size + '</td><td class ="text-center">' + item[i].time + '</td></tr>';
            }
            switch (type) {
                case 'nav':
                    $(ojb).nextAll().detach();
                    break;
                case 'ml':
                    var nav = '<a  class="breadcrumb-item" href="javascript:;"  onclick="getList(\'nav\',\'' + dir + '\',this)">' + $(ojb).text() + '</a>';
                    $('#nav').append(nav);
                    break;
                case 'hash':
                    var nav = '<a class="breadcrumb-item"></a>',
                        dir2 = '',
                        arr = dir.split('/'),
                        Max = arr.length - 1;
                    for (var i = 0; i < Max; i++) {
                        dir2 += arr[i] + '/';
                        nav += '<a  class="breadcrumb-item" href="javascript:;"  onclick="getList(\'nav\',\'' + dir2 + '\',this)">' + arr[i] + '</a>';
                    }
                    $('#nav').html(nav);
                    break;
            }
            $('#list').html(str);
            layer.closeAll('loading');
        }
    });
}
// 下载验证
function downVerify(type, title, dir) {
    if (verify) {
        layer.open({
            type: 1,
            skin: 'layui-layer-rim',
            area: ['350px', '250px'],
            title: '你需要证明你不是机器人',
            content: '<div class="captcha">' +
                '<div id="embed-captcha"></div>' +
                '<p id="wait" class="show">正在加载验证码......</p>' +
                '<p id="notice" class="hide">请先完成验证</p>' +
                '</div>'
        });
        $.ajax({
            url: 'Ajax.php?act=verify&t=' + (new Date()).getTime(),
            dataType: 'json',
            success: function (data) {
                initGeetest({
                    'gt': data.gt,
                    'challenge': data.challenge,
                    'new_captcha': data.new_captcha,
                    'product': 'embed',
                    'offline': !data.success
                }, handlerEmbed);
            }
        });
        var handlerEmbed = function (captchaObj) {
            $('#embed-submit').click(function (e) {
                var validate = captchaObj.getValidate();
                if (!validate) {
                    $('#notice')[0].className = 'show';
                    setTimeout(function () {
                        $('#notice')[0].className = 'hide';
                    }, 1000);
                    e.preventDefault();
                }
            });

            captchaObj.appendTo('#embed-captcha');
            captchaObj.onReady(function () {
                $('#wait')[0].className = 'hide';
            });
            captchaObj.onSuccess(function () {
                var result = captchaObj.getValidate(),
                    gtData = {
                        'geetest_challenge': result.geetest_challenge,
                        'geetest_validate': result.geetest_validate,
                        'geetest_seccode': result.geetest_seccode
                    };
                layer.closeAll('page');
                Preview(type, title, dir, gtData);
            });
        };
    } else {
        Preview(type, title, dir);
    }
}
// 下载、预览文件
function Preview(type, title, dir, gtData = {}) {
    var postData = Object.assign({ 'dir': dir }, gtData);
    $.ajax({
        url: 'Ajax.php?act=getUrl',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function (data) {
            if (data.code == 2) {
                layer.alert('错误信息：' + data.msg, { title: '下载出错', icon: 2 });
                return;
            }
            var url = data.data.url,
                result,
                lw = '60%',
                lh = '70%';
            switch (type) {
                case 'mp3':
                    lw = 'auto', lh = 'auto';
                    result = '<audio width="100%" height="100%" controls><source src="' + url + '" type="audio/mpeg">您的浏览器不支持该音频格式。</audio>';
                    break;
                case 'mp4':
                    if (window.screen.width < 1024) {
                        lw = '100%', lh = 'auto';
                    };
                    result = '<video width="100%" height="100%" controls style="object-fit: fill"><source src="' + url + '" type="video/mp4">您的浏览器不支持该视频格式。</video>';
                    break;
                case 'jpg': case 'png': case 'bmp': case 'gif': case 'ico':
                    layer.photos({
                        photos: { 'data': [{ 'src': url }] },
                        anim: 5
                    });
                    return;
                default:
                    window.location.href = url;
                    return;
            }
            layer.open({
                type: 1,
                title: title + ' - 文件预览',
                area: [lw, lh],
                shadeClose: true,
                shade: 0.8,
                content: result
            });
        }
    })
}
// 前台登录
function login() {
    var index = layer.msg('登录验证中，请稍候', { icon: 16, time: false, shade: 0.8 }),
        indexpass = $('#indexpass').val();
    $.ajax({
        url: 'Ajax.php?act=login',
        type: 'POST',
        dataType: 'json',
        data: { 'indexpass': indexpass },
        success: function (data) {
            setTimeout(function () {
                layer.close(index);
                layer.msg(data.msg, { icon: data.code, time: 1000 });
                setTimeout(function () {
                    if (data.code == 1) {
                        layer.closeAll();
                        doHash();
                    }
                }, 500)
            }, 500);
        }
    })
    return false;
}
// 设置hash
function setHash(dir) {
    location.hash = '/' + dir;
    nowHash = dir;
}
// 监控hash
function doHash() {
    if (location.hash.substr(-1) != '/') {
        getList();
        return;
    }
    var hash = decodeURI(location.hash.replace('#/', ''));
    if (hash != nowHash) {
        getList('hash', hash);
    }
}