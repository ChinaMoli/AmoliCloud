// 网页初始化
$(function () {
    $.ajax({
        url: 'Ajax.php?act=getConfig',
        dataType: "json",
        success: function (data) {
            // 检测是否安装
            if (!data.install) {
                layer.open({
                    title: '提示',
                    content: '你还没有安装程序，点击确定安装',
                    icon: 2,
                    yes: function () { window.location.href = 'install/index.php'; }
                })
                return;
            }
            // 保留版权是对作者最大的尊重。
            console.info('欢迎使用 AmoliCloud!\n当前版本：' + data.version + ' \n作者：无名氏Studio(https://wums.cn)\n官网：Amoli私有云(https://www.amoli.co)\nGithub：https://github.com/ChinaMoli/AmoliCloud');
            $("title,.navbar-brand").prepend(data.name);// 配置前端信息
            $("#record").text(data.record);
            if (data.log) {
                getList("");
            } else {
                layer.open({
                    type: 1,
                    title: '请输入查看密码',
                    area: ['350px', 'auto'],
                    content: '<div class="container text-right"><br><form onsubmit="return login();"><div class="input-group mb-3"><div class="input-group-prepend"><span class="input-group-text">密码：</span></div><input type="text" class="form-control" id="indexpass"></div><input type="submit" class="btn btn-primary" value="确认"><div><p></p></div></form></div>'
                });
            }
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
        default:
            result = "file";
    }
    return result;
}
// 获取文件和目录
function getList(ojb, ListNav, thats) {
    layer.load(0, { shade: false });
    if (ojb == "") {// 网页加载
        var dir = "";
    } else if (ojb == "nav") {// 点击导航栏
        var dir = ListNav;
    } else { //点击目录
        var dir = ListNav + $(ojb).text() + "/";
    }
    $.ajax({
        url: 'Ajax.php?act=getList&dir=' + dir,
        dataType: "json",
        error: function () {
            $("#list").html('<th class="text-center" colspan="4">请求错误</th>');
            layer.closeAll('loading');
        },
        success: function (data) {
            $("title,.navbar-brand").prepend(data.name);// 配置前端信息
            var str = "";
            $("#list").html('');
            var item = data.data;
            for (var i = 0, Max = item.length; i < Max; i++) {
                if (item[i].type == "wjj") {
                    var name = '<a href="javascript:;" onclick="getList(this,\'' + dir + '\')">' + item[i].name + '</a>';
                } else {
                    var name = '<a href="javascript:;" onclick="Preview(\'' + item[i].type + '\',\'' + item[i].name + '\', \'' + dir + item[i].name + '\')">' + item[i].name + '</a>';
                }
                str += '<tr><td><svg class="icon" aria-hidden="true"><use xlink:href="#icon-' + getType(item[i].type) + '"></use></svg></td><td>' + name + '</td><td class ="text-right">' + item[i].size + '</td><td class ="text-center">' + item[i].time + '</td></tr>';
            }
            if (ojb == "") {
                $("#nav").html("");
            }
            if (ojb != "nav") {
                var nav = '<a  class="breadcrumb-item" href="javascript:;"  onclick="getList(\'nav\',\'' + dir + '\',this)">' + $(ojb).text() + '</a>';
                $("#nav").append(nav);
            } else {
                $(thats).nextAll().detach();
            }
            $("#list").append(str);
            layer.closeAll('loading');
        }
    });
}
// 下载文件
function DownFlie(dir) {
    $.ajax({
        url: 'Ajax.php?act=getUrl&dir=' + dir,
        dataType: "json",
        success: function (data) {
            if (data.msg) {
                window.location.href = data.url;
            } else {
                layer.alert('错误代码：<br>' + data.msg, { icon: 2 });
            }
        }
    })
}
// 预览文件
function Preview(type, title, dir) {
    $.ajax({
        url: 'Ajax.php?act=getUrl&dir=' + dir,
        dataType: "json",
        success: function (data) {
            var result;
            switch (type) {
                case "mp3":
                    result = '<audio width="100%" height="100%" controls><source src="' + data.url + '" type="audio/mpeg">您的浏览器不支持该音频格式。</audio>';
                    break;
                case "mp4":
                    result = '<video width="100%" height="100%" controls><source src="' + data.url + '" type="video/mp4">您的浏览器不支持该视频格式。</video>';
                    break;
                case "jpg": case "png": case "bmp": case "gif": case "ico":
                    result = '<img src="' + data.url + '" class="img-fluid">';
                    break;
                default:
                    DownFlie(dir);
                    return;
            }
            layer.open({
                type: 1,
                title: title + ' - 文件预览',
                area: ['80%', '70%'],
                shadeClose: true,
                shade: 0.8,
                content:
                    '<div class="container">'
                    + '<br>'
                    + '<div class="text-center">'
                    + result
                    + '</div>'
                    + '<br>'
                    + '<div class="text-right">'
                    + '<button type="button" class="btn btn-outline-secondary btn-sm"><a class="dropdown-item" target="_blank" href="' + data.url + '"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-down"></use></svg>下载</a></button>'
                    + '<p> </p>'
                    + '</div>'
                    + '</div>'
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
        type: "post",
        dataType: "json",
        data: { 'indexpass': indexpass },
        success: function (data) {
            if (data.msg) {
                setTimeout(function () { layer.closeAll(); layer.msg('登录成功', { icon: 1, time: 1000 }); getList(""); }, 500);
            } else {
                setTimeout(function () { layer.close(index); layer.msg('密码错误！', { icon: 2, time: 1000 }); }, 500);
            }
        }
    })
    return false;
}