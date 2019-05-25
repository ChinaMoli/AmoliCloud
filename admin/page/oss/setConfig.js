layui.use(['form', 'layer', 'jquery'], function () {
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : top.layer,
		$ = layui.jquery;

	// 加载OSS配置
	$.ajax({
		url: "../../ajax.php?act=systemParameter",
		type: "get",
		dataType: "json",
		success: function (data) {
			var item = data.data;
			$(".bucket").val(item.bucket);
			$(".endpoint").val(item.endpoint);
			$(".accessKeyId").val(item.accessKeyId);
			$(".accessKeySecret").val(item.accessKeySecret);
			$(".ossdomain").val(item.ossdomain);
		}
	})

	// 修改OSS配置
	form.on("submit(ossConfig)", function (data) {
		var bucket = $('.bucket').val();
		var endpoint = $('.endpoint').val();
		var accessKeyId = $('.accessKeyId').val();
		var accessKeySecret = $('.accessKeySecret').val();
		var ossdomain = $('.ossdomain').val();
		var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
		$.ajax({
			url: "../../ajax.php?act=ossconfig",
			type: "post",
			data: { 'bucket': bucket, 'endpoint': endpoint, 'accessKeyId': accessKeyId, 'accessKeySecret': accessKeySecret, 'ossdomain': ossdomain },
			dataType: "json",
			success: function (data) {
				setTimeout(function () { layer.close(index); layer.msg(data.data.msg, { icon: 1, time: 1000 }); }, 500);
			}
		})
		return false;
	})
})
