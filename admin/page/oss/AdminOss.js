layui.use(['layer', 'jquery', 'table'], function () {
	var layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery,
		table = layui.table;

	// 文件列表
	var tableIns = table.render({
		elem: '#List',
		url: '../../ajax.php?act=getList&dir=',
		toolbar: '#Headbar',
		defaultToolbar: ['', '', ''],
		size: 'sm',
		cols: [[
			{ field: 'type', title: '', templet: function (d) { return getType(d.type); }, width: 46, align: "center", unresize: true },
			{ field: 'name', title: '文件名', event: 'setSign', style: 'cursor: pointer;' },
			{ field: 'size', title: '文件大小', width: 100, align: 'right', unresize: true },
			{ field: 'time', title: '更新时间', width: 150, align: 'center', unresize: true },
			{ title: '操作', width: 120, templet: '#ListBar', align: "center", unresize: true }
		]]
	});

	// 工具栏事件
	table.on('toolbar(List)', function (obj) {
		if (obj.event == 'Upload') {
			layer.open({
				type: 1,
				title: '上传文件',
				area: ['70%', '80%'],
				content: '<div class="page-container"><blockquote class="layui-elem-quote">1.文件上传位置为当前目录 当前上传目录：<span id="NowDir">' + $('#NowDir').val() + '</span><br>2.为不影响你的正常使用，请上传完成后再关闭此窗口</blockquote><div class="layui-upload"><div class="layui-upload-list"><table class="layui-table"><thead><tr><th>文件名</th><th>大小</th><th>进度</th><th>状态</th></tr></thead><tbody id="demoList"></tbody></table></div></div><div id="container"><a id="selectfiles" href="javascript:;" class="layui-btn layui-btn-normal">选择文件</a><a id="postfiles" href="javascript:;" class="layui-btn">开始上传</a></div><script type="text/javascript" src="page/oss/Upload/js/plupload/plupload.full.min.js"></script><script type="text/javascript" src="page/oss/Upload/js/upload.js"></script></div>'
			});
		}
	});

	// 表格被点击
	table.on('tool(List)', function (obj) {
		var data = obj.data;
		switch (obj.event) {
			case 'setSign':
				if (data.type == 'wjj') {// 加载目录表单
					var NowDir = $('#NowDir').val() + data.name + '/'; // 取当前目录
					tableIns.reload({
						url: '../../ajax.php?act=getList&dir=' + NowDir
					});
					$('#NowDir').val(NowDir);// 置当前目录
				}
				if (data.type == 'reply') {// 返回上一层
					var NowDir = $('#NowDir').val(), // 取当前目录
						n = NowDir.split('/'), // 将当前目录进行数组切割
						max = n.length - 2,
						UpDir = '';
					if (max > 0) {
						for (var i = 0; i < max; i++) {
							UpDir += n[i] + '/';
						}
					}
					tableIns.reload({
						url: '../../ajax.php?act=getList&dir=' + UpDir
					});
					$('#NowDir').val(UpDir);// 置当前目录
				}
				break;
			case 'down':// 下载文件
				var Nowfile = $('#NowDir').val() + data.name // 取文件名
				$.ajax({
					url: "../../ajax.php?act=Downfile&dir=" + Nowfile,
					type: "get",
					dataType: "json",
					success: function (data) {
						var item = data.data;
						if (item.msg == 'ok') {
							window.location.href = data.data.url;
						} else {
							layer.alert('错误代码：<br>' + item.msg, { icon: 2 });
						}
					}
				})
				break;
			case 'del':// 删除文件
				var Nowfile = $('#NowDir').val() + data.name // 取文件名
				layer.confirm('删除后无法恢复，确定删除吗？', { icon: 0 }, function (indoex) {
					var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
					$.ajax({
						url: "../../ajax.php?act=Delfile&dir=" + Nowfile,
						type: "get",
						dataType: "json",
						success: function (data) {
							var msg = data.data.msg;
							if (msg == 'ok') {
								obj.del();
								layer.msg('操作成功！', { icon: 1, time: 1800 });
							} else {
								layer.alert('错误代码：<br>' + msg, { icon: 2 });
							}
							layer.close(index);
						}
					})
				})
				break;
		}
	})

	// 输出文件图标
	function getType(type) {
		var result = "";
		var style = "";
		switch (type) {
			case "zip": case "rar": case "7z":
				result = "file-archive-o";
				break;
			case "jpg": case "png": case "bmp": case "gif": case "ico":
				result = "file-image-o";
				break;
			case "php": case "css": case "htm": case "jsp": case "html": case "js":
				result = "file-code-o";
				break;
			case "exe":
				result = "cog";
				break;
			case "mp4":
				result = "file-movie-o";
				break;
			case "mp3":
				result = "file-audio-o";
				break;
			case "txt":
				result = "file-text-o";
				break;
			case "reply":
				result = "reply";
				style = 'style="color:#00c1de"';
				break;
			case "wjj":
				result = "folder";
				style = 'style="color:#FFB800"';
				break;
			default:
				result = "file";
		}
		return '<i ' + style + ' class="fa fa-' + result + ' fa-fw"></i>';
	}
})
