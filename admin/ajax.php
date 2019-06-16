<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once '../app/class/Amoli.class.php';
$C = new Config('../Config');
$Amoli = new Amoli();
$user = $C->get('user');
$pass = $C->get('pass');
$act = $_GET['act'];
$dir = $_GET['dir'];
$oss = $C->get('oss');
$cos = $C->get('cos');
$type = $C->get('type', 'local');
$Cookie = $_COOKIE['Admin_' . $user];
// 判断是否登录(排除登录操作)
if (!isset($Cookie) || $Cookie != $pass) {
    if ($act != 'login') {
        echo json_encode(['code' => '-1', 'data' => null]);
        return;
    }
}
switch ($act) {
    case 'getList': // 加载目录
        $reply = [];
        ($dir) ? $reply[] = ['type' => 'reply', 'name' => '返回上一层', 'size' => '', 'time' => ''] : '';
        switch ($type) {
            case 'local':
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
                $list = $Amoli->getLocalList($dir);
                break;
            case 'oss':
                $dir = $oss['osshost'] . $dir;
                $list = $Amoli->getOssList($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $dir);
                break;
            case 'cos':
                $dir = $cos['coshost'] . $dir;
                $list = $Amoli->getCosList($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $dir);
                break;
        }
        $result = array_merge($reply, (array)$list);
        break;
    case 'Downfile': // 下载文件
        $object = $Amoli->DirEncoding($_SERVER["QUERY_STRING"]);
        switch ($type) {
            case 'local':
                $url = '/' . $C->get('localhost') . $object;
                $result = ['code' => '0', 'msg' => true, 'url' => $url];
                break;
            case 'oss':
                $object = $oss['osshost'] . $object;
                $result = $Amoli->getOssUrl($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $oss['ossdomain'], $object);
                break;
            case 'cos':
                $object = $cos['coshost'] . $object;
                $result = $Amoli->getCosUrl($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $object);
                break;
        }
        break;
    case 'NewFolder': // 新建目录
        //判断目录格式
        if ((strrpos($dir, '/') + 1) != strlen($dir)) {
            $result = ['code' => '0', 'data' => ['msg' => '目录格式有误！']];
            echo json_encode($result);
            return;
        };
        switch ($type) {
            case 'local':
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
                mkdir($dir, 0777, true) ? $msg = true : $msg = false;
                $result = ['code' => '0', 'msg' => $msg];
                break;
            case 'oss':
                $dir = $oss['osshost'] . $dir;
                $result = $Amoli->OssNewFolder($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $dir);
                break;
            case 'cos':
                $dir = $cos['coshost'] . $dir;
                $result = $Amoli->CosNewFolder($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $dir);
                break;
        }
        break;
    case 'Delfile': // 删除文件
        $object = $Amoli->DirEncoding($_SERVER["QUERY_STRING"]);
        switch ($type) {
            case 'local':
                $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $object, true);
                if (unlink($file)) {
                    $result = ['msg' => 'ok'];
                } else {
                    $result = ['msg' => '删除失败！'];
                }
                break;
            case 'oss':
                $object = $oss['osshost'] . $object;
                $result = $Amoli->getOssDel($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $object);
                break;
            case 'cos':
                $object = $cos['coshost'] . $object;
                $result = $Amoli->getCosDel($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $object);
                break;
        }
        break;
    case 'Upfile': // 上传文件    
        switch ($type) {
            case 'local':
                $filename = $_FILES['file']['name'];
                if ($filename) {
                    $source = $_FILES['file']['tmp_name'];
                    $dir = $Amoli->getEncoding($C->get('localhost') . $dir . $filename, true);
                    $destination = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
                    move_uploaded_file($source, $destination) ? $msg = '上传成功' : $msg = '上传失败';
                    $data = ['msg' => $msg, 'name' => $filename];
                }
                break;
            case 'oss':
                $dir = $oss['osshost'] . $dir;
                $data = $Amoli->OssUpfile($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $dir);
                break;
            case 'cos':
                $dir = $cos['coshost'] . $dir;
                $data = $Amoli->CosUpfile($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $dir);
                break;
        }
        $result = array_merge(['type' => $type], (array)$data);
        break;
    case 'systemParameter': // 系统基本参数
        $result = $C->get() + [
            'tongji' => ($_GET['bool']) ? file_get_contents('../static/js/tj.js') : '',
            'server' => PHP_OS,
            'host' => $_SERVER['HTTP_HOST'],
            'root' => $_SERVER['DOCUMENT_ROOT'],
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'php_version' => PHP_VERSION,
            'upload_max' => get_cfg_var("file_uploads") ? get_cfg_var("upload_max_filesize") : '空间不允许上传'
        ];
        break;
    case 'webconfig': // 网站配置
        $C->set('name', $_POST['name']);
        $type = $_POST['type'];
        $C->set('type', $type);
        $C->set('localhost', $_POST['localhost']);
        $oss = $_POST['oss'];
        $C->set('oss', $oss);
        $cos = $_POST['cos'];
        $C->set('cos', $cos);
        $C->set('indexpass', $_POST['indexpass']);
        $C->set('record', $_POST['record']);
        $msg = $C->save();
        //统计代码
        $file_strm = fopen('../static/js/tj.js', 'w');
        if (!$file_strm) $msg = '写入文件失败，请赋予 tj.js 文件写权限！';
        fwrite($file_strm, $_POST['tongji']);
        fclose($file_strm);
        //设置跨域规则
        switch ($type) {
            case 'oss':
                $Amoli->OssCors($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret']);
                break;
            case 'cos':
                $Amoli->CosCors($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey']);
                break;
        }
        ($msg == true) ? $result = ['msg' => '修改成功！'] : $result = ['msg' => $msg];
        break;
    case 'login': // 登录后台
        $POST_user = $_POST['user'];
        $POST_pass = MD5($_POST['pass'] . '$$Www.Amoli.Co$$');
        $loginTime = date('Y-m-d H:i:s');
        if ($POST_user == $user && $POST_pass == $pass) {
            setcookie('Admin_' . $POST_user, $POST_pass, time() + 3600 * 24); // 写入Cookies
            $result = ['msg' => '登录成功'];
        } else {
            $result = ['msg' => '帐号或者密码错误'];
        }
        $C->set('loginTime', $loginTime);
        $C->save();
        break;
    case 'logout': // 退出登录
        setcookie('Admin_' . $user, '', time() - 1552294270);
        exit('<script language="javascript">alert("您已成功注销本次登陆！");window.location.href="index.html";</script>');
        $result = ['msg' => '成功退出登录'];
        break;
    case 'lock': // 锁屏验证
        $lockPwd = MD5($_POST['lockPwd'] . '$$Www.Amoli.Co$$');
        if ($lockPwd == $pass) {
            $result = ['msg' => 'ok'];
        } else {
            $result = ['msg' => 'no'];
        }
        break;
    case 'setaccount': // 修改后台帐号密码
        $POST_user = $_POST['user'];
        $POST_pass = MD5($_POST['pass'] . '$$Www.Amoli.Co$$');
        $POST_confirmPwd = $_POST['confirmPwd'];
        if ($POST_pass != $pass) {
            $result = ['msg' => '密码错误，请重新输入！'];
        } else {
            $C->set('user', $POST_user);
            $C->set('pass', MD5($POST_confirmPwd . '$$Www.Amoli.Co$$'));
            $msg = $C->save();
            ($msg) ? $result = ['msg' => '修改成功！'] : $result = ['msg' => $msg];
        }
        break;
    default:
        $result = ['msg' => 'No Act!'];
}
$result = ['code' => '0', 'data' => $result];
echo json_encode($result);
