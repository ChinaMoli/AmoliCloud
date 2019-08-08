<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/app/class/Amoli.class.php';
$C = new Config('Config');
$Amoli = new Amoli();
$s = $_GET['s'];
if (!$s) header('Location: ./');
$info = $Amoli->getShare($s);
if (!$info) header('Location: ./');
$title = $info['name'] . ' - ' . $C->get('name');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="shortcut icon" href="favicon.ico" />
    <script src="https://at.alicdn.com/t/font_1186130_04lkv4r5pub2.js"></script>
    <script src="static/js/jquery.min.js"></script>
    <style>
        body{font-family:Tahoma,Arial,Roboto,”Droid Sans”,”Helvetica Neue”,”Droid Sans Fallback”,”Heiti SC”,sans-self;font-size:16px;color:#333;margin:0;padding:0;background-color:transparent;border-color:transparent;-webkit-appearance:none;-webkit-tap-highlight-color:rgba(0,0,0,0);-webkit-tap-highlight-color:rgba(0,0,0,0.0)}a{text-decoration:none}.user-top{height:30px;padding:10px}.user-ico{float:left}.user-ico-img{width:30px;height:30px;border-radius:50%;position:absolute}.user-ico-div{width:30px;height:25px;border-radius:50%;padding-top:5px}.user-name{float:left;font-size:14px;line-height:30px;color:#888;margin-left:10px}.appfile{text-align:center;padding-top:50px}.appico{width:80px;height:80px;margin:auto;border-radius:20px;box-shadow:0px 1px 10px rgba(0,0,0,0.07)}.appname{font-size:22px;line-height:1.4em;padding:20px 20px 10px 20px;text-overflow:ellipsis;overflow:hidden}.appinfo{font-size:14px;color:#888;padding-bottom:20px}.appinfotime{margin-right:10px}.applink{padding-top:20px;padding-bottom:10px}.appa{color:#fff;background:#86d2ff;background:#5bccff;background:#33c5ff;border-radius:7px;display:block;margin:auto;line-height:42px;height:42px}.appdown{position:initial;bottom:initial;left:initial;right:initial;z-index:7;width:130px;margin:auto}
        /* 滑块验证码 */
        .captcha{width:100%;height:100%;display:flex;align-items:center;justify-content:center}#embed-captcha{width:300px;margin:0 auto;margin-top:-50px}#embed-captcha:empty{display:none}#embed-captcha::after{color:gray;display:block;font-size:13px;margin-top:5px;content:'使用你的手，缓慢且准确的滑过去！'}.show{display:block}.hide{display:none}#notice{color:red}
    </style>
</head>

<body>
    <div class="user-top">
        <a href="./">
            <div class="user-ico">
                <div class="user-ico-img" style="background:url(favicon.ico);background-size:100%;background-repeat:no-repeat;background-position:50%;">
                </div>
                <div class="user-ico-div">
                </div>
            </div>
            <div class="user-name"><?php echo $C->get('name'); ?></div>
        </a>
    </div>

    <div class="appfile">
        <svg class="appico" aria-hidden="true">
            <use xlink:href="#icon-<?php echo $info['type']; ?>"></use>
        </svg>
        <div class="appname"><?php echo $info['name']; ?></div>
        <div class="appinfo"><span class="appinfotime"><?php echo $info['time']; ?></span><span><?php echo $info['size']; ?></span></div>
        <div class="appdown">
            <div id="applink" class="applink">
                <a href="javascript:;" id="down" target="_blank" class="appa">下载</a>
            </div>
        </div>
        <br>
        <p align="center">
            <span>Copyright &copy; 2019 Powered by <a target="_blank" href="https://www.amoli.co">Amoli.Co</a>&nbsp;</span><span id='record'></span>
        </p>
    </div>
    <script src="static/layer/layer.js"></script>
    <script src="static/js/gt.js"></script>
    <script>
        $(document).on('click', '#down', function() {
            var verify = '<?php echo $C->get('verify', false); ?>';
            if (verify) {
                layer.open({
                    type: 1,
                    skin: 'layui-layer-rim',
                    area: ['350px', '250px'],
                    title: '你需要证明你不是机器人',
                    content: '<div class="captcha">' +
                        '<div id="embed-captcha"></div>' +
                        '<p id="wait" class="show">正在加载验证码......</p>' +
                        '<p id="notice" class="hide">请先完成验证</p>' +
                        '</div>'
                });
                $.ajax({
                    url: 'Ajax.php?act=verify&t=' + (new Date()).getTime(),
                    dataType: 'json',
                    success: function(data) {
                        initGeetest({
                            'gt': data.gt,
                            'challenge': data.challenge,
                            'new_captcha': data.new_captcha,
                            'product': 'embed',
                            'offline': !data.success
                        }, handlerEmbed);
                    }
                });
                var handlerEmbed = function(captchaObj) {
                    $('#embed-submit').click(function(e) {
                        var validate = captchaObj.getValidate();
                        if (!validate) {
                            $('#notice')[0].className = 'show';
                            setTimeout(function() {
                                $('#notice')[0].className = 'hide';
                            }, 1000);
                            e.preventDefault();
                        }
                    });
                    captchaObj.appendTo('#embed-captcha');
                    captchaObj.onReady(function() {
                        $('#wait')[0].className = 'hide';
                    });
                    captchaObj.onSuccess(function() {
                        var result = captchaObj.getValidate(),
                            gtData = {
                                'geetest_challenge': result.geetest_challenge,
                                'geetest_validate': result.geetest_validate,
                                'geetest_seccode': result.geetest_seccode
                            };
                        layer.closeAll('page');
                        down(gtData);
                    });
                };
            } else {
                down();
            }
        })
        function down(gtData = {}) {
            var postData = Object.assign({ 'dir': '<?php echo $info['dir']; ?>' }, gtData);
            $.ajax({
                url: 'Ajax.php?act=getUrl',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(data) {
                    if (data.code == 1) {
                        window.location.href = data.data.url;
                    } else {
                        layer.alert('错误信息：' + data.msg, {
                            title: '下载出错',
                            icon: 2
                        });
                    }
                }
            })
        }
    </script>
    <script src="static/js/tj.js"></script>
</body>

</html>