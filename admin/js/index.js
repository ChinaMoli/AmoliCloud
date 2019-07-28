layui.use(['form', 'layer', 'jquery'], function () {
    var form = layui.form,
        layer = layui.layer,
        $ = layui.jquery;

    // 判断是否已登录
    $(function () {
        $.ajax({
            url: 'ajax.php',
            dataType: 'json',
            success: function (data) {
                if (data.msg == 'No Act!') {
                    window.location.href = 'home.html';
                }
            }
        })
    })

    //登录按钮
    form.on('submit(login)', function (data) {
        var index = layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
        $.ajax({
            url: 'ajax.php?act=login',
            type: 'POST',
            data: data.field,
            dataType: 'json',
            success: function (data) {
                setTimeout(function () {
                    layer.close(index);
                    layer.msg(data.msg, { icon: data.code, time: 1000 });
                    setTimeout(function () {
                        if (data.code == 1) {
                            window.location.href = 'home.html';
                        }
                    }, 1500);
                }, 500);
            }
        })
        return false;
    })

    //表单输入效果
    $(".loginBody .input-item").click(function (e) {
        e.stopPropagation();
        $(this).addClass("layui-input-focus").find(".layui-input").focus();
    })
    $(".loginBody .layui-form-item .layui-input").focus(function () {
        $(this).parent().addClass("layui-input-focus");
    })
    $(".loginBody .layui-form-item .layui-input").blur(function () {
        $(this).parent().removeClass("layui-input-focus");
        if ($(this).val() != '') {
            $(this).parent().addClass("layui-input-active");
        } else {
            $(this).parent().removeClass("layui-input-active");
        }
    })
})
