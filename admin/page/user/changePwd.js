layui.use(['form'], function () {
    var form = layui.form,
        $ = layui.jquery;

    // 加载用户名
    $.ajax({
        url: "../../ajax.php?act=systemParameter",
        type: "get",
        dataType: "json",
        success: function (data) {
            $(".user").val(data.data.user);
        }
    })

    //添加验证规则
    form.verify({
        user: function (value, item) {
            if (value.length < 5) {
                return "用户名长度不能小于5位";
            }
        },
        pass: function (value, item) {
            if (value.length < 6) {
                return "密码长度不能小于6位";
            }
        },
        newPwd: function (value, item) {
            if (value.length < 6) {
                return "密码长度不能小于6位";
            }
        },
        confirmPwd: function (value, item) {
            if (!new RegExp($(".oldPwd").val()).test(value)) {
                return "两次输入密码不一致，请重新输入！";
            }
        }
    })

    // 修改后台帐号密码
    form.on("submit(changePwd)", function (data) {
        var user = $('.user').val();
        var pass = $('.pass').val();
        var confirmPwd = $('.confirmPwd').val();
        var index = layui.layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
        $.ajax({
            url: "../../ajax.php?act=setaccount",
            type: "post",
            data: { 'user': user, 'pass': pass, 'confirmPwd': confirmPwd },
            dataType: "json",
            success: function (data) {
                if (data.data.msg == "修改成功！") {
                    setTimeout(function () { layer.close(index); layer.msg(data.data.msg, { icon: 1, time: 1000 }); }, 500);
                } else {
                    setTimeout(function () { layer.close(index); layer.msg(data.data.msg, { icon: 2, time: 1000 }); }, 500);
                }
            }
        })
        return false;
    })
})