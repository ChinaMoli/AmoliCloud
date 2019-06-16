var NowDir = encodeURI($('#NowDir').text()),
    host, multipart_params;
$.ajax({
    url: 'ajax.php?act=Upfile&dir=' + NowDir,
    dataType: "json",
    async: false,
    success: function (data) {
        var item = data.data;
        switch (item.type) {
            case 'local':
                host = 'ajax.php?act=Upfile&dir=' + NowDir;
                break;
            case 'oss':
                host = item.host;
                multipart_params = {
                    'key': item.dir + '${filename}',
                    'policy': item.policy,
                    'OSSAccessKeyId': item.accessid,
                    'success_action_status': '200',
                    'signature': item.signature
                };
                break;
            case 'cos':
                host = item.host;
                multipart_params = {
                    'key': item.dir + '${filename}',
                    'policy': item.policy,
                    'q-sign-algorithm': 'sha1',
                    'success_action_status': '200',
                    'q-ak': item.secretId,
                    'keytime':item.keytime,
                    'signature': item.signature
                };
                break;
        }
    }
});
var uploader = new plupload.Uploader({
    runtimes: 'html5,flash,silverlight,html4',
    browse_button: 'selectfiles',
    container: document.getElementById('container'),
    url: host,
    flash_swf_url: 'plupload/Moxie.swf',
    silverlight_xap_url: 'plupload/Moxie.xap',
    multipart_params: multipart_params,
    filters: {
        prevent_duplicates: true
    },
    init: {
        PostInit: function () {
            document.getElementById('demoList').innerHTML = '';
            document.getElementById('postfiles').onclick = function () {
                uploader.start();
                return false;
            };
        },
        FilesAdded: function (up, files) {
            plupload.each(files, function (file) {
                document.getElementById('demoList').innerHTML += '<tr id="' + file.id + '"><td>' + file.name + '</td><td>' + plupload.formatSize(file.size) + '</td><td><div class="layui-progress layui-progress-big " lay-showpercent="true""><div id="' + file.id + '_jdt" class="layui-progress-bar layui-bg-blue" style="width:0%"><span class="layui-progress-text">0%</span></div></div></td><td id="' + file.id + '_zt">等待上传</td></tr>';
            });
        },
        BeforeUpload: function (up, file) {
            document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #009688;">正在上传</span>';
        },
        UploadProgress: function (up, file) {
            var jdt = document.getElementById(file.id + '_jdt');
            jdt.style.width = file.percent + '%';
            jdt.getElementsByTagName('span')[0].innerHTML = file.percent + '%';
        },
        FileUploaded: function (up, file, info) {
            if (info.status == 200 || info.status == 203) {
                document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #1E9FFF;">上传成功</span>';
            }
            else {
                document.getElementById(file.id + '_zt').innerHTML = '<span style="color: #FF5722;">上传失败</span>';
            }
        }
    }
});
uploader.init();