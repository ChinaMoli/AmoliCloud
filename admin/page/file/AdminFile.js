layui.use(['layer', 'jquery', 'table'], function () {
	var layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery,
		table = layui.table,
		tableIns;

	// 文件列表
	var loading = layer.load(0, { shade: false });
	tableIns = table.render({
		elem: '#List',
		url: '../../ajax.php?act=getList',
		method: 'POST',
		toolbar: '<div class="layui-btn-container"><button class="layui-btn layui-btn-sm layui-bg-red" lay-event="Upload">上传文件</button><button class="layui-btn layui-btn-sm layui-bg-cyan" lay-event="NewFolder">新建目录</button><button class="layui-btn layui-btn-sm layui-bg-blue" lay-event="break">刷新目录</button></div>',
		defaultToolbar: ['', '', ''],
		size: 'sm',
		cols: [[
			{ field: 'type', title: '', templet: function (d) { return getType(d.type); }, width: 46, align: "center", unresize: true },
			{ field: 'name', title: '文件名', event: 'setSign', style: 'cursor: pointer;', sort: true },
			{ field: 'size', title: '文件大小', width: 100, align: 'right', unresize: true, sort: true },
			{ field: 'time', title: '上传日期', width: 150, align: 'center', unresize: true, sort: true },
			{ title: '操作', width: 180, templet: '#ListBar', align: "center", unresize: true }
		]]
	});
	layer.close(loading);

	function reload(NowDir) {
		var loading = layer.load(0, { shade: false });
		tableIns.reload({
			url: '../../ajax.php?act=getList',
			method: 'POST',
			where: { 'dir': NowDir }
		});
		layer.close(loading);
	}
	// 工具栏事件
	table.on('toolbar(List)', function (obj) {
		var NowDir = $('#NowDir').val();
		switch (obj.event) {
			case 'Upload':
				layer.open({
					type: 1,
					title: '上传文件 - Amoli私有云',
					area: ['70%', '80%'],
					content: '<div class="page-container"><blockquote class="layui-elem-quote">1.文件上传位置为当前目录 当前上传目录：<span id="NowDir" style="color:#FF5722;">' + NowDir + '</span><br>2.为不影响你的正常使用，请上传完成后再关闭此窗口</blockquote><div class="layui-upload"><div class="layui-upload-list"><table class="layui-table"><colgroup><col><col><col><col><col width="100"></colgroup><thead><tr><th>文件名</th><th>大小</th><th>进度</th><th>状态</th><th>操作</th></tr></thead><tbody id="fileList"></tbody></table></div></div><div id="container"><a id="selectfiles" href="javascript:;" class="layui-btn layui-btn-normal">选择文件</a><a id="postfiles" href="javascript:;" class="layui-btn">开始上传</a></div><script type="text/javascript" src="../static/js/jquery.min.js"></script></div><script type="text/javascript" src="page/file/upload/plupload/plupload.full.min.js"></script><script type="text/javascript" src="page/file/upload/Upload.js?v=4.2.1"></script>',
					end: function () { reload(NowDir); }
				});
				break;
			case 'NewFolder':
				layer.prompt({
					title: '新建目录',
					btn: ['确认'],
					content: '<div class="layui-form-item">'
						+ '<label class="layui-form-label">目录名</label>'
						+ '<div class="layui-input-block">'
						+ '<input type="text" value="" placeholder="请输入目录名" class="layui-layer-input Folder">'
						+ '<br>'
						+ '<p>'
						+ '<span class="layui-red">目录命名规范</span>：<br>'
						+ '1. 可用数字、中英文和可见字符的组合<br>'
						+ '2. 必须用<span class="layui-red"> / </span>结尾<br>'
						+ '3. 用<span class="layui-red"> / </span>分割路径，可快速创建子目录<br>'
						+ '4. 不允许: <span class="layui-red">文件夹为空</span>；<span class="layui-red">连续 / </span>；<span class="layui-red">以 / 开头</span><br>'
						+ '5. 不允许以<span class="layui-red"> .. </span>作为文件夹名称'
						+ '</p>'
						+ '</div>'
						+ '</div>',
					yes: function (index, layero) {
						$.ajax({
							url: '../../ajax.php?act=NewFolder',
							type: 'POST',
							data: { 'dir': NowDir + layero.find('.Folder').val() },
							dataType: 'json',
							success: function (data) {
								layer.close(index);
								layer.msg(data.msg, { icon: data.code, time: 1000 });
							}
						})
					},
					end: function () { reload(NowDir); }
				});
				break;
			case 'break':
				reload(NowDir);
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
					reload(NowDir);
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
					reload(UpDir);
					$('#NowDir').val(UpDir);// 置当前目录
				}
				break;
			case 'share':// 分享文件
				var loading = layer.load(0, { shade: false });
				$.ajax({
					url: '../../ajax.php?act=share',
					type: 'POST',
					data: { 'dir': NowDir, 'size': data.size },
					dataType: 'json',
					success: function (data) {
						var msg = '文件名：' + obj.data.name + '<br>分享地址：<br>' + data.data.url;
						layer.alert(msg, {
							title: '分享成功！',
							icon: 1,
							btn: '复制',
							yes: function (index) {
								layer.close(index);
								var oInput = $('<input type="text" id="shareUrl">');
								oInput.val(data.data.url);
								$('.childrenBody').append(oInput);
								oInput.select();
								document.execCommand('Copy');
								$(oInput).attr('type', 'hidden');
								layer.msg('已复制至剪切板', { icon: 1, time: 1000 });
							}
						});
						layer.close(loading);
					}
				})
				break;
			case 'down':// 下载文件
				$.ajax({
					url: '../../ajax.php?act=Downfile',
					type: 'POST',
					data: { 'dir': NowDir },
					dataType: 'json',
					success: function (data) {
						if (data.code == 1) {
							window.location.href = data.data.url;
						} else {
							layer.alert('错误信息：' + data.msg, { title: '下载出错', icon: 2 });
						}
					}
				})
				break;
			case 'del':// 删除文件
				layer.confirm('删除后无法恢复，确定删除吗？', { icon: 0 }, function (indoex) {
					var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
					$.ajax({
						url: '../../ajax.php?act=Delfile',
						type: 'POST',
						data: { 'dir': NowDir },
						dataType: 'json',
						success: function (data) {
							layer.msg(data.msg, { icon: data.code, time: 1800 });
							if (data.code == 1) {
								obj.del();
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
			case "apk":
				result = "file_apk";
				break;
			default:
				result = "file";
		}
		return '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-' + result + '"></use></svg>';
	}
})
