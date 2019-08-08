layui.use(['form', 'layer', 'jquery'], function () {
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery;

	// 加载网站设置
	$.ajax({
		url: '../../ajax.php?act=systemParameter&bool=true',
		dataType: 'json',
		success: function (data) {
			var item = data.data,
				oss = item.oss,
				cos = item.cos;
			$('.name').val(item.name);
			(!item.type) ? item.type = 'local' : '';//默认为本地存储
			$('input[type="radio"][name="type"][value="' + item.type + '"]').prop('checked', true);
			$('input[type="radio"][name="verify"][value="' + item.verify + '"]').prop('checked', true);
			RadioOn(item.type);
			$(".localhost").val(item.localhost);

			$(".OssBucket").val(oss.bucket);
			$(".endpoint").val(oss.endpoint);
			$(".accessKeyId").val(oss.accessKeyId);
			$(".accessKeySecret").val(oss.accessKeySecret);
			$(".ossdomain").val(oss.ossdomain);
			$(".osshost").val(oss.osshost);

			$(".CosBucket").val(cos.bucket);
			$(".region").val(cos.region);
			$(".secretId").val(cos.secretId);
			$(".secretKey").val(cos.secretKey);
			$(".coshost").val(cos.coshost);

			$(".indexpass").val(item.indexpass);
			$(".record").val(item.record);
			$(".tongji").val(item.tongji);
		}
	})

	// 修改网站配置
	form.on("submit(WebConfig)", function (data) {
		var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
		$.ajax({
			url: '../../ajax.php?act=webconfig',
			type: 'POST',
			data: data.field,
			dataType: 'json',
			success: function (data) {
				setTimeout(function () { layer.close(index); layer.msg(data.msg, { icon: data.code, time: 1000 }); }, 500);
			}
		})
		return false;
	})

	// 单选框点击
	form.on('radio()', function (data) {
		RadioOn(data.value);
	})
	function RadioOn(type = 'local') {
		switch (type) {
			case 'local':
				$("legend").text('本地存储 - 配置');
				$(".LocalConfig").removeClass('layui-hide');// 显示
				$(".OssConfig").addClass('layui-hide');// 隐藏
				$(".CosConfig").addClass('layui-hide');// 隐藏
				$(".OssBucket,.endpoint,.accessKeyId,.accessKeySecret").removeAttr('lay-verify');// 关闭Oss必填
				$(".CosBucket,.region,.secretId,.secretKey").removeAttr('lay-verify');// 关闭Cos必填
				break;
			case 'oss':
				$("legend").text('OSS存储 - 配置');
				$(".LocalConfig").addClass('layui-hide');
				$(".OssConfig").removeClass('layui-hide');
				$(".CosConfig").addClass('layui-hide');
				$(".endpoint").attr('lay-verify', 'url');
				$(".OssBucket,.accessKeyId,.accessKeySecret").attr('lay-verify', 'required');
				$(".CosBucket,.region,.secretId,.secretKey").removeAttr('lay-verify');
				break;
			case 'cos':
				$("legend").text('COS存储 - 配置');
				$(".LocalConfig").addClass('layui-hide');
				$(".OssConfig").addClass('layui-hide');
				$(".CosConfig").removeClass('layui-hide');
				$(".CosBucket,.region,.secretId,.secretKey").attr('lay-verify', 'required');
				$(".OssBucket,.endpoint,.accessKeyId,.accessKeySecret").removeAttr('lay-verify');
		}
		form.render();// 更新单选框状态
	}

	// 目录判断
	form.verify({
		host: function (value) {
			if (value) {
				if (value.substr(0, 1) == '/') {
					return '目录名称不能以/开头';
				} else if (value.substr(-1) != '/') {
					return '目录名称必须以/结尾';
				}
			}
		},
		Endpoint: function (value) {
			var regex =/^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i;
			if(!regex.test(value)){
				return 'Endpoint格式错误';
			}
		}
	})
})