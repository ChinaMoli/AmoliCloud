var NowDir = $('#NowDir').text(),
    host, multipart_params;
$.ajax({
    url: 'ajax.php?act=Upfile',
    type: 'POST',
    data: { 'dir': NowDir },
    dataType: 'json',
    async: false,
    success: function (data) {
        var item = data.data;
        switch (item.type) {
            case 'local':
                host = 'ajax.php?act=Upfile';
                multipart_params = {
                    'dir': NowDir
                };
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
                    'keytime': item.keytime,
                    'signature': item.signature
                };
                break;
        }
    }
});
var uploader = new plupload.Uploader({
    runtimes: 'html5,flash,silverlight,html4',
    browse_button: 'selectfiles',
    url: host,
    flash_swf_url: 'page/file/upload/plupload/Moxie.swf',
    silverlight_xap_url: 'page/file/upload/plupload/Moxie.xap',
    multipart_params: multipart_params,
    filters: {
        prevent_duplicates: true
    },
    init: {
        PostInit: function () {
            $('#postfiles').click(function () {
                uploader.start();
                return false;
            });
        },
        FilesAdded: function (up, files) {
            plupload.each(files, function (file) {
                $('#fileList').append(
                    '<tr id="' + file.id + '">'
                    + '<td>' + file.name + '</td>'
                    + '<td>' + plupload.formatSize(file.size) + '</td>'
                    + '<td><div class="layui-progress layui-progress-big " lay-showpercent="true""><div id="' + file.id + '_jdt" class="layui-progress-bar layui-bg-blue" style="width:0%"><span class="layui-progress-text">0%</span></div></div></td>'
                    + '<td id="' + file.id + '_zt">等待上传</td>'
                    + '<td>'
                    + '<button class="layui-btn layui-btn-xs layui-btn-danger" onclick="dodel(' + "'" + file.id + "'" + ')">删除</button>'
                    + '</td></tr>'
                );
            });
        },
        BeforeUpload: function (up, file) {
            $('#' + file.id + '_zt').html('<span style="color: #009688;">正在上传</span>')
        },
        UploadProgress: function (up, file) {
            var jdt = $('#' + file.id + '_jdt');
            jdt.css('width', file.percent + '%');
            jdt.children('span').text(file.percent + '%');
        },
        FileUploaded: function (up, file, info) {
            var zt = $('#' + file.id + '_zt');
            if (info.status == 200 || info.status == 203) {
                zt.html('<span style="color: #1E9FFF;">上传成功</span>');
            }
            else {
                zt.html('<span style="color: #FF5722;">上传失败</span>');
            }
        }
    }
});
uploader.init();
// 删除上传队列文件
function dodel(files) {
    uploader.removeFile(files);
    $('#' + files).remove();
}