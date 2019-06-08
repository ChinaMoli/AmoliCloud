var accessid = '',
    accesskey = '',
    host = '',
    policyBase64 = '',
    signature = '',
    callbackbody = '',
    filename = '',
    key = '',
    expire = 0,
    g_object_name = '',
    g_object_name_type = '',
    now = Date.parse(new Date()) / 1000,
    NowDir = document.getElementById('NowDir').textContent,
    timestamp = now;

function send_request() {
    var xmlhttp = null;
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (xmlhttp != null) {
        var serverUrl = 'ajax.php?act=ossUpload&dir=' + NowDir;
        xmlhttp.open("GET", serverUrl, false);
        xmlhttp.send(null);
        return xmlhttp.responseText
    }
};

function get_signature() {
    now = timestamp = Date.parse(new Date()) / 1000;
    if (expire < now + 3) {
        body = send_request()
        var obj = eval("(" + body + ")"),
            item = obj.data;
        host = item['host']
        policyBase64 = item['policy']
        accessid = item['accessid']
        signature = item['signature']
        expire = parseInt(item['expire'])
        callbackbody = item['callback']
        key = item['dir']
        return true;
    }
    return false;
};

function random_string(len) {
    len = len || 32;
    var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var maxPos = chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

function get_suffix(filename) {
    pos = filename.lastIndexOf('.')
    suffix = ''
    if (pos != -1) {
        suffix = filename.substring(pos)
    }
    return suffix;
}

function set_upload_param(up, filename, ret) {
    if (ret == false) {
        ret = get_signature()
    }
    g_object_name = key;
    if (filename != '') {
        suffix = get_suffix(filename)
        g_object_name += filename
    }
    new_multipart_params = {
        'key': g_object_name,
        'policy': policyBase64,
        'OSSAccessKeyId': accessid,
        'success_action_status': '200',
        'callback': callbackbody,
        'signature': signature,
    };

    up.setOption({
        'url': host,
        'multipart_params': new_multipart_params
    });

    up.start();
}

var uploader = new plupload.Uploader({
    runtimes: 'html5,flash,silverlight,html4',
    browse_button: 'selectfiles',
    //multi_selection: false,
    container: document.getElementById('container'),
    flash_swf_url: 'plupload/Moxie.swf',
    silverlight_xap_url: 'plupload/Moxie.xap',
    url: 'http://oss.aliyuncs.com',

    filters: {
		/* 限制上传文件的后戳名
        mime_types : [
        { title : "Image files", extensions : "jpg,gif,png,bmp" }, 
        { title : "Zip files", extensions : "zip,rar" }
        ],
		*/
        max_file_size: '1000MB', //最大只能上传1000MB的文件
        prevent_duplicates: true //不允许选取重复文件
    },
    init: {
        PostInit: function () {
            document.getElementById('demoList').innerHTML = '';
            document.getElementById('postfiles').onclick = function () {
                set_upload_param(uploader, '', false);
                return false;
            };
        },
        FilesAdded: function (up, files) {
            plupload.each(files, function (file) {
                document.getElementById('demoList').innerHTML += '<tr id="' + file.id + '"><td>' + file.name + '</td><td>' + plupload.formatSize(file.size) + '</td><td><div class="layui-progress layui-progress-big " lay-showpercent="true""><div id="' + file.id + '_jdt" class="layui-progress-bar layui-bg-blue" style="width:0%"><span class="layui-progress-text">0%</span></div></div></td><td id="' + file.id + '_zt">等待上传</td></tr>';
            });
        },
        BeforeUpload: function (up, file) {
            set_upload_param(up, file.name, true);
            document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #009688;">正在上传</span>';
        },
        UploadProgress: function (up, file) {
            var jdt = document.getElementById(file.id + '_jdt');
            jdt.style.width = file.percent + '%';
            jdt.getElementsByTagName('span')[0].innerHTML = file.percent + '%';
        },
        FileUploaded: function (up, file, info) {
            if (info.status == 200 || info.status == 203) {
                document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #5FB878;">上传成功</span>';
            }
            else {
                document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #FF5722;">上传失败</span>';
            }
        }
    }
});

uploader.init();