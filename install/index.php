<?php
error_reporting(0); // 关闭错误提示
if (file_exists('install.lock')) {
    echo '<div class="alert alert-warning">您已经安装过，如需重新安装请删除<font color=red> install/install.lock </font>文件后再安装！</div>';
    exit;
}
?>
<html lang="zh-CN">

<head>
    <title>安装向导 - Amoli私有云</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#4d545d">
    <link rel="shortcut icon" href="../favicon.ico" />
    <link href="../admin/layui/css/layui.css" rel="stylesheet">
	<script type="text/javascript" src="../admin/layui/layui.js"></script>
</head>
<style type="text/css">
    body{text-align:center}.header{position:fixed;left:0;top:0;width:80%;height:60px;line-height:60px;background:#000;padding:0 10%;z-index:10000}.header h1{color:#fff;font-size:20px;font-weight:600;text-align:center}.install-box{margin:100px auto 0;background:#fff;border-radius:10px;padding:20px;overflow:hidden;box-shadow:5px 5px 15px#888888;display:inline-block;width:680px;min-height:500px}.protocol{text-align:left;height:400px;overflow-y:auto;padding:10px;color:#333}.protocol h2{text-align:center;font-size:16px;color:#000}.step-btns{padding:20px 0 10px 0}.copyright{padding:25px 0}.copyright,.copyright a{color:#ccc}.layui-table td,.layui-table th{text-align:left}.layui-table tbody tr.no{background-color:#f00;color:#fff}
</style>

<body>
    <div class="header">
        <h1>感谢您选择Amoli私有云系统</h1>
    </div>
    <?php
    $step = $_GET['step'];
    switch ($step) {
        case '':
        case '1':
            echo '<div class="install-box">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>Amoli私有云用户协议 适用于所有用户</legend>
            <div class="protocol">
            <p>
                请您在使用(Amoli私有云)前仔细阅读如下条款。包括免除或者限制作者责任的免责条款及对用户的权利限制。您的安装使用行为将视为对本《用户许可协议》的接受，并同意接受本《用户许可协议》各项条款的约束。<br><br>
                一、安装和使用：<br>
                (Amoli私有云)是免费和开源提供给您使用的，您可安装无限制数量副本。 您必须保证在不进行非法活动，不违反国家相关政策法规的前提下使用本软件。<br><br>
                二、免责声明： <br>
                本源码并无附带任何形式的明示的或暗示的保证，包括任何关于本源码的适用性, 无侵犯知识产权或适合作某一特定用途的保证。<br>
                在任何情况下，对于因使用本软件或无法使用本软件而导致的任何损害赔偿，作者均无须承担法律责任。作者不保证本软件所包含的资料,文字、图形、链接或其它事项的准确性或完整性。作者可随时更改本软件，无须另作通知。<br>
                所有由用户自己制作、下载、使用的第三方信息数据和插件所引起的一切版权问题或纠纷，本软件概不承担任何责任。<br><br>
                三、协议规定的约束和限制：<br>
                禁止去除(Amoli私有云)源码里的版权信息，商业授权版本可去除后台界面及前台界面的相关版权信息。<br>
                禁止在(Amoli私有云)整体或任何部分基础上发展任何派生版本、修改版本或第三方版本用于重新分发。<br><br>
                <strong>版权所有 &copy; 2018-2019，Amoli私有云,保留所有权利</strong>。
            </p>
            </div>
        </fieldset>
        <div class="step-btns">
            <a href="?step=2" class="layui-btn layui-btn-big layui-btn-normal">同意协议并安装系统</a>
        </div>
        </div>';
            break;
        case '2':
            if (phpversion() < '5.6') {
                $version = 'no';
                $fr = '<a href="javascript:;" class="layui-btn layui-btn-big layui-btn-disabled fr">进行下一步</a>';
            } else {
                $version = 'ok';
                $fr = '<a href="?step=3" class="layui-btn layui-btn-big layui-btn-normal fr">进行下一步</a>';
            }
            echo '<div class="install-box">
            <fieldset class="layui-elem-field layui-field-title">
                <legend>运行环境检测</legend>
            </fieldset>
            <table class="layui-table" lay-skin="line">
                <thead>
                    <tr>
                        <th>环境名称</th>
                        <th>当前配置</th>
                        <th>所需配置</th>
                    </tr> 
                </thead>
                <tbody>
                    <tr class="ok">
                        <td>操作系统</td>
                        <td>WINNT</td>
                        <td>Windows/Unix</td>
                    </tr>
                    <tr class="' . $version . '">
                        <td>推荐PHP版本</td>
                        <td>' . phpversion() . '</td>
                        <td>5.6及以上</td>
                    </tr>
                            </tbody>
            </table>
            <table class="layui-table" lay-skin="line">
                <thead>
                    <tr>
                        <th>目录/文件</th>
                        <th>所需权限</th>
                        <th>当前权限</th>
                    </tr> 
                </thead>
                <tbody>
                    <tr class="ok">
                        <td>/Config.php</td>
                        <td>读写</td>
                        <td>未知</td>
                    </tr>
                </tbody>
            </table>
            <div class="step-btns">
                <a href="?step=1" class="layui-btn layui-btn-primary layui-btn-big fl">返回上一步</a>
                ' . $fr . '
                </div>
        </div>';
            break;
        case '3':
            echo '<div class="install-box">
            <fieldset class="layui-elem-field layui-field-title">
                <legend>网站信息配置</legend>
            </fieldset>
            <form class="layui-form layui-form-pane" action="?step=4" method="post">
                <div class="layui-form-item">
                    <label class="layui-form-label">网站名称</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="name" lay-verify="required" value="Amoli云盘">
                    </div>
                    <div class="layui-form-mid" style="color: #FF5722;">您的网站名称 *必填</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">前台密码</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="indexpass">
                    </div>
                    <div class="layui-form-mid layui-word-aux">留空即为关闭密码</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">网站备案号</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="record">
                    </div>
                    <div class="layui-form-mid layui-word-aux">网站备案号</div>
                </div>
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>管理账号设置</legend>
                </fieldset>
                <div class="layui-form-item">
                    <label class="layui-form-label">管理员账号</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="user" lay-verify="required|user">
                    </div>
                    <div class="layui-form-mid" style="color: #FF5722;">管理员账号最少5位 *必填</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">管理员密码</label>
                    <div class="layui-input-inline">
                        <input type="password" class="layui-input" name="pass" lay-verify="required|pass">
                    </div>
                    <div class="layui-form-mid" style="color: #FF5722;">管理员密码最少6位 *必填</div>
                </div>
                <div class="step-btns">
                    <a href="?step=2" class="layui-btn layui-btn-primary layui-btn-big fl">返回上一步</a>
                    <button lay-submit="" lay-filter="ossConfig" class="layui-btn layui-btn-big layui-btn-normal fr">立即执行安装</button>
                </div>
            </form>
        </div>
        <script>
        layui.use(["form"], function () {
            var form = layui.form;
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
                }
            })
        })
        </script>
        ';
            break;
        case '4':
            $name = $_POST['name'];
            $indexpass = $_POST['indexpass'];
            $record = $_POST['record'];
            $user = $_POST['user'];
            $pass = MD5($_POST['pass'] . '$$Www.Amoli.Co$$');
            if ($name && $user && $pass) {
                require_once '../app/class/Amoli.class.php';
                $C = new Config('../Config');
                // 存储数据
                $C->set('name', $name); // 网站名称
                $C->set('indexpass', $indexpass); // 前台密码
                $C->set('record', $record); // 网站备案号
                $C->set('user', $user); // 后台账户
                $C->set('pass', $pass); // 后台密码
                $msg = $C->save();
                if ($msg) {
                    file_put_contents('install.lock', 'www.amoli.co') ? $result = '安装完成!' : $result = 'install.lock写入失败!';
                } else {
                    $result = $msg;
                }
            } else {
                $result = '网站名称、管理员账号、管理员密码不允许为空！';
            }
            echo '<div class="install-box">
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>安装提示</legend>
                </fieldset>
                <h1>' . $result . '</h1>
                <div class="step-btns">
                <a href="/" class="layui-btn layui-btn-primary layui-btn-big fl">返回首页</a>
                <a href="../admin/index.html" class="layui-btn layui-btn-big layui-btn-normal fr">前往后台</a>
                </div>
            </div>';
            break;
    }
    ?>
    <div class="copyright">&copy; 2018-2019<a href="http://www.amoli.co" target="_blank"> Amoli.Co</a> All Rights Reserved.</div>
</body>

</html>