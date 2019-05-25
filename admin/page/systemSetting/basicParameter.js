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
			$(".indexpass").val(item.indexpass);
			$(".record").val(item.record);
		}
	})

	// 修改网站配置
	form.on("submit(WebConfig)", function (data) {
		var name = $('.name').val();
		var indexpass = $('.indexpass').val();
		var record = $('.record').val();
		var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
		$.ajax({
			url: "../../ajax.php?act=webconfig",
			type: "post",
			data: { 'name': name, 'indexpass': indexpass, 'record': record },
			dataType: "json",
			success: function (data) {
				setTimeout(function () { layer.close(index); layer.msg(data.data.msg, { icon: 1, time: 1000 }); }, 500);
			}
		})
		return false;
	})

})
