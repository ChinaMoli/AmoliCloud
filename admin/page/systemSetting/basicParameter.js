layui.use(['form', 'layer', 'jquery'], function () {
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery;

	// 加载网站设置
	$.ajax({
		url: "../../ajax.php?act=systemParameter",
		type: "get",
		dataType: "json",
		success: function (data) {
			var item = data.data;
			$(".name").val(item.name);
			RadioOn(item.type);
			$(".localhost").val(item.localhost);
			$(".bucket").val(item.bucket);
			$(".endpoint").val(item.endpoint);
			$(".accessKeyId").val(item.accessKeyId);
			$(".accessKeySecret").val(item.accessKeySecret);
			$(".ossdomain").val(item.ossdomain);
			$(".indexpass").val(item.indexpass);
			$(".record").val(item.record);
		}
	})

	// 修改网站配置
	form.on("submit(WebConfig)", function (data) {
		var name = $('.name').val(),
			type = $("input[name='type']:checked").val(),
			localhost = $('.localhost').val(),
			bucket = $('.bucket').val(),
			endpoint = $('.endpoint').val(),
			accessKeyId = $('.accessKeyId').val(),
			accessKeySecret = $('.accessKeySecret').val(),
			ossdomain = $('.ossdomain').val(),
			indexpass = $('.indexpass').val(),
			record = $('.record').val(),
			index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
		$.ajax({
			url: "../../ajax.php?act=webconfig",
			type: "post",
			data: {
				'name': name,
				'type': type,
				'localhost': localhost,
				'bucket': bucket,
				'endpoint': endpoint,
				'accessKeyId': accessKeyId,
				'accessKeySecret': accessKeySecret,
				'ossdomain': ossdomain,
				'indexpass': indexpass,
				'record': record
			},
			dataType: "json",
			success: function (data) {
				setTimeout(function () { layer.close(index); layer.msg(data.data.msg, { icon: 1, time: 1000 }); }, 500);
			}
		})
		return false;
	})

	// 单选框点击
	form.on("radio()", function (data) {
		RadioOn(data.value);
	})
	function RadioOn(type = 'local') {
		var radios = $('input[type="radio"][name="type"]');
		if (type == "local") {
			$("legend").text('本地存储 - 配置');
			radios.eq(0).prop("checked", true);// 选择本地
			radios.eq(1).prop("checked", false);// 取消OSS
			$(".LocalConfig").removeClass('layui-hide');// 显示
			$(".OssConfig").addClass('layui-hide');// 隐藏
			$(".bucket,.endpoint,.accessKeyId,.accessKeySecret").removeAttr('lay-verify');// 关闭必填
		} else {
			$("legend").text('OSS存储 - 配置');
			radios.eq(0).prop("checked", false);
			radios.eq(1).prop("checked", true);
			$(".LocalConfig").addClass('layui-hide');
			$(".OssConfig").removeClass('layui-hide');
			$(".endpoint").attr('lay-verify', 'url');
			$(".bucket,.accessKeyId,.accessKeySecret").attr('lay-verify', 'required');
		}
		form.render();// 更新单选框状态
	}
})