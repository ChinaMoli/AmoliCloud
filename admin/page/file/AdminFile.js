layui.use(['layer', 'jquery', 'table'], function () {
	var layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery,
		table = layui.table,
		tableIns;

	// 加载存储类型
	$.ajax({
		url: "../../ajax.php?act=systemParameter",
		type: "get",
		dataType: "json",
		success: function (data) {
			var Headbar = '<div class="layui-btn-container">';
			if (data.data.type == 'oss') {
				Headbar += '<button class="layui-btn layui-btn-sm layui-bg-red" lay-event="ossUpload">上传文件</button>';
			} else {
				Headbar += '<button class="layui-btn layui-btn-sm" lay-event="localUpload">上传文件</button>';
			}
			Headbar += '<button class="layui-btn layui-btn-sm layui-bg-blue" lay-event="break">刷新目录</button></div>'
			// 文件列表
			tableIns = table.render({
				elem: '#List',
				url: '../../ajax.php?act=getList&dir=',
				toolbar: Headbar,
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
		}
	})

	// 工具栏事件
	table.on('toolbar(List)', function (obj) {
		switch (obj.event) {
			case 'ossUpload':
				layer.open({
					type: 1,
					title: 'OSS - 上传文件',
					area: ['70%', '80%'],
					content: '<div class="page-container"><blockquote class="layui-elem-quote">1.文件上传位置为当前目录 当前上传目录：<span id="NowDir" style="color:#FF5722;">' + $('#NowDir').val() + '</span><br>2.为不影响你的正常使用，请上传完成后再关闭此窗口</blockquote><div class="layui-upload"><div class="layui-upload-list"><table class="layui-table"><thead><tr><th>文件名</th><th>大小</th><th>进度</th><th>状态</th></tr></thead><tbody id="demoList"></tbody></table></div></div><div id="container"><a id="selectfiles" href="javascript:;" class="layui-btn layui-btn-normal">选择文件</a><a id="postfiles" href="javascript:;" class="layui-btn">开始上传</a></div><script type="text/javascript" src="page/file/upload/plupload/plupload.full.min.js"></script><script type="text/javascript" src="page/file/upload/ossUpload.js"></script></div>'
				});
				break;
			case 'localUpload':
				layer.open({
					type: 1,
					title: '本地 - 上传文件',
					area: ['70%', '80%'],
					content: '<div class="page-container"><blockquote class="layui-elem-quote">1.文件上传位置为当前目录 当前上传目录：<span id="NowDir" style="color:#FF5722;">' + $('#NowDir').val() + '</span><br>2.为不影响你的正常使用，请上传完成后再关闭此窗口</blockquote><div class="layui-upload"><div class="layui-upload-list"><table class="layui-table"><thead><tr><th>文件名</th><th>大小</th><th>进度</th><th>状态</th></tr></thead><tbody id="demoList"></tbody></table></div><button type="button"class="layui-btn layui-btn-normal"id="selectfiles">选择文件</button><button type="button"class="layui-btn"id="postfiles">开始上传</button></div></div></script><script type="text/javascript" src="page/file/upload/localUpload.js"></script>'
				});
				break;
			case 'newfolder':
				alert('等待发布');
				break;
			case 'break':
				var NowDir = $('#NowDir').val();
				tableIns.reload({
					url: '../../ajax.php?act=getList&dir=' + NowDir
				});
				break;
		}
	});

	// 表格被点击
	table.on('tool(List)', function (obj) {
		var data = obj.data,
			NowDir = $('#NowDir').val() + data.name;
		switch (obj.event) {
			case 'setSign':
				if (data.type == 'wjj') {// 加载目录表单
					NowDir = NowDir + '/'; // 取当前目录
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
				$.ajax({
					url: "../../ajax.php?act=Downfile&dir=" + NowDir,
					type: "get",
					dataType: "json",
					success: function (data) {
						var item = data.data;
						if (item.msg) {
							window.location.href = item.url;
						} else {
							layer.alert('错误代码：<br>' + item.msg, { icon: 2 });
						}
					}
				})
				break;
			case 'del':// 删除文件
				layer.confirm('删除后无法恢复，确定删除吗？', { icon: 0 }, function (indoex) {
					var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
					$.ajax({
						url: "../../ajax.php?act=Delfile&dir=" + NowDir,
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
			case "reply":
				result = "fanhui";
				break;
			default:
				result = "file";
		}
		return '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-' + result + '"></use></svg>';
	}
})
