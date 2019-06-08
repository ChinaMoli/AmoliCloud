layui.use(['upload', 'element', 'layer'], function () {
    var $ = layui.jquery
        , upload = layui.upload
        , element = layui.element
        , layer = layui.layer;
    // 单位换算
    function getFilesize(num) {
        var p = 0,
            format = ' B';
        if (num > 0 && num < 1024) {
            p = 0;
            return num + format;
        }
        if (num >= 1024 && num < Math.pow(1024, 2)) {
            p = 1;
            format = ' KB';
        }
        if (num >= Math.pow(1024, 2) && num < Math.pow(1024, 3)) {
            p = 2;
            format = ' MB';
        }
        if (num >= Math.pow(1024, 3) && num < Math.pow(1024, 4)) {
            p = 3;
            format = ' GB';
        }
        num /= Math.pow(1024, p);
        return Math.round(num * 100) / 100 + format;
    }
    //创建监听函数
    var xhrOnProgress = function (fun) {
        xhrOnProgress.onprogress = fun; //绑定监听
        //使用闭包实现监听绑
        return function () {
            //通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
            var xhr = $.ajaxSettings.xhr();
            //判断监听函数是否为函数
            if (typeof xhrOnProgress.onprogress !== 'function')
                return xhr;
            //如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
            if (xhrOnProgress.onprogress && xhr.upload) {
                xhr.upload.onprogress = xhrOnProgress.onprogress;
            }
            return xhr;
        }
    }

    //多文件列表示例
    var demoListView = $('#demoList')
        , NowDir = $('#NowDir').text()
        , uploadListIns = upload.render({
            elem: '#selectfiles'
            , url: 'ajax.php?act=localUpload&dir=' + NowDir
            , accept: 'file'
            , multiple: true
            , xhr: xhrOnProgress
            , progress: function (value, obj) {//上传进度回调 value进度值
                $("#demoList").find('.layui-progress ').each(function () {
                    if ($(this).attr("file") == obj.name) {
                        var progressBarName = $(this).attr("lay-filter");
                        var percent = Math.floor((value.loaded / value.total) * 100);//计算百分比
                        element.progress(progressBarName, percent + '%')//设置页面进度条
                    }
                })

            }
            , auto: false
            , bindAction: '#postfiles'
            , choose: function (obj) {
                var files = this.files = obj.pushFile();
                var count;
                obj.preview(function (index, file, result) {
                    count++;
                    var tr = $(['<tr id="upload-' + index + '">'
                        , '<td>' + file.name + '</td>'
                        , '<td>' + getFilesize(file.size) + '</td>'
                        , '<td><div file="' + file.name + '" class="layui-progress layui-progress-big" lay-showpercent="true" lay-filter="progressBar' + count + '"><div class="layui-progress-bar layui-bg-blue" lay-percent="0%"><span class="layui-progress-text">0%</span></div></div>'
                        , '</td>'
                        , '<td>等待上传</td>'
                        , '</tr>'].join(''));

                    //单个重传
                    tr.find('.demo-reload').on('click', function () {
                        obj.upload(index, file);
                    });

                    //删除
                    tr.find('.demo-delete').on('click', function () {
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });

                    demoListView.append(tr);
                });
            }
            , done: function (res, index, upload) {
                if (res.code == 0) { //上传成功
                    var tr = demoListView.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(3).html('<span style="color: #5FB878;">上传成功</span>');
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            }
            , error: function (index, upload) {
                var tr = demoListView.find('tr#upload-' + index)
                    , tds = tr.children();
                tds.eq(3).html('<span style="color: #FF5722;">上传失败</span>');
            }
        });
});