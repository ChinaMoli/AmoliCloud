layui.use(['form'], function () {
    var form = layui.form,
        $ = layui.jquery;

    // 加载用户名
    $.ajax({
        url: '../../ajax.php?act=systemParameter',
        dataType: 'json',
        success: function (data) {
            $('.user').val(data.data.user);
        }
    })

    //添加验证规则
    form.verify({
        user: function (value) {
            if (value.length < 5) {
                return "用户名长度不能小于5位";
            }
        },
        pass: function (value) {
            if (value.length < 6) {
                return "密码长度不能小于6位";
            }
        },
        newPwd: function (value) {
            if (value.length < 6) {
                return "密码长度不能小于6位";
            }
        },
        confirmPwd: function (value) {
            if (!new RegExp($(".oldPwd").val()).test(value)) {
                return "两次输入密码不一致，请重新输入！";
            }
        }
    })

    // 修改后台帐号密码
    form.on('submit(changePwd)', function (data) {
        var index = layui.layer.msg('数据提交中，请稍候', { icon: 16, time: false, shade: 0.8 });
        $.ajax({
            url: '../../ajax.php?act=setaccount',
            type: 'POST',
            data: data.field,
            dataType: "json",
            success: function (data) {
                setTimeout(function () {
                    layer.close(index);
                    layer.msg(data.msg, { icon: data.code, time: 1000 });
                }, 500);
            }
        })
        return false;
    })
})