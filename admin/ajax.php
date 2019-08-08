<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once '../app/class/Amoli.class.php';
$C = new Config('../Config');
$Amoli = new Amoli();
$act = $_GET['act'];
$oss = $C->get('oss');
$cos = $C->get('cos');
$user = $C->get('user');
$pass = $C->get('pass');
$type = $C->get('type', 'local');
$Cookie = $_COOKIE['AmoliAdmin_' . $user];
// 判断是否登录(排除登录操作)
if (!isset($Cookie) || $Cookie != $pass) {
    if ($act != 'login') {
        echo json_encode(['code' => 2, 'msg' => '你未登录，请先登录！']);
        return;
    }
}
switch ($act) {
    case 'getList': // 加载目录
        $dir = $_POST['dir'];
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
        $list = array_merge($reply, (array) $list);
        $result = ['code' => 0, 'msg' => '获取成功！', 'data' => $list];
        break;
    case 'Downfile': // 下载文件
        $object = $_POST['dir'];
        switch ($type) {
            case 'local':
                $url = '/' . $C->get('localhost') . $object;
                $result = ['code' => 1, 'msg' => '获取成功！', 'data' => ['url' => $url]];
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
        $dir = $_POST['dir'];
        //判断目录格式
        if ((strrpos($dir, '/') + 1) != strlen($dir)) {
            $result = ['code' => 0, 'msg' => '目录格式有误！'];
            echo json_encode($result);
            return;
        };
        switch ($type) {
            case 'local':
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
                mkdir($dir, 0777, true) ? $result = ['code' => 1, 'msg' => '创建成功！'] : $result = ['code' => 2, 'msg' => '创建失败！'];
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
    case 'share': // 分享文件
        $object = $_POST['dir'];
        $time = date('Y-m-d H:i:s');
        $size = $_POST['size'];
        $data = rawurlencode(base64_encode($object . '{/}' . $time . '{/}' . $size));
        $result = ['code' => 1, 'msg' => '获取成功！', 'data' => ['url' => $Amoli->postShare($data)]];
        break;
    case 'Delfile': // 删除文件
        $object = $_POST['dir'];
        switch ($type) {
            case 'local':
                $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $object, true);
                if (unlink($file)) {
                    $result = ['code' => 1, 'msg' => '删除成功！'];
                } else {
                    $result = ['code' => 2, 'msg' => '删除失败！'];
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
        $dir = $_POST['dir'];
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
        $data = array_merge(['type' => $type], (array) $data);
        $result = ['code' => 1, 'msg' => '获取成功！', 'data' => $data];
        break;
    case 'systemParameter': // 系统基本参数
        $data = $C->get() + [
            'tongji' => ($_GET['bool']) ? file_get_contents('../static/js/tj.js') : '',
            'server' => PHP_OS,
            'host' => $_SERVER['HTTP_HOST'],
            'root' => $_SERVER['DOCUMENT_ROOT'] . dirname(dirname($_SERVER['SCRIPT_NAME'])),
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'php_version' => PHP_VERSION,
            'upload_max' => get_cfg_var("file_uploads") ? get_cfg_var("upload_max_filesize") : '空间不允许上传',
            'time' => date('Y-m-d H:i:s', time())
        ];
        $result = ['code' => 1, 'msg' => '获取成功！', 'data' => $data];
        break;
    case 'webconfig': // 网站配置
        $C->set('name', $_POST['name']);
        $type = trim($_POST['type']);
        $C->set('type', $type);
        $C->set('localhost', trim($_POST['localhost']));
        $oss = [
            'bucket' => trim($_POST['OssBucket']),
            'endpoint' => trim($_POST['endpoint']),
            'accessKeyId' => trim($_POST['accessKeyId']),
            'accessKeySecret' => trim($_POST['accessKeySecret']),
            'ossdomain' => trim($_POST['ossdomain']),
            'osshost' => trim($_POST['osshost'])
        ];
        $C->set('oss', $oss);
        $cos = [
            'bucket' => trim($_POST['CosBucket']),
            'region' => trim($_POST['region']),
            'secretId' => trim($_POST['secretId']),
            'secretKey' => trim($_POST['secretKey']),
            'coshost' => trim($_POST['coshost'])
        ];
        $C->set('cos', $cos);
        $C->set('indexpass', trim($_POST['indexpass']));
        ($_POST['verify'] == 'true') ? $verify = true : $verify = false;
        $C->set('verify', $verify);
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
        ($msg == true) ? $result = ['code' => 1, 'msg' => '修改成功！'] : $result = ['code' => 2, 'msg' => $msg];
        break;
    case 'login': // 登录后台
        $POST_user = $_POST['user'];
        $POST_pass = MD5($_POST['pass'] . '$$Www.Amoli.Co$$');
        $loginTime = date('Y-m-d H:i:s');
        if ($POST_user == $user && $POST_pass == $pass) {
            setcookie('AmoliAdmin_' . $POST_user, $POST_pass, time() + 3600 * 24); // 写入Cookies
            $result = ['code' => 1, 'msg' => '登录成功！'];
        } else {
            $result = ['code' => 2, 'msg' => '帐号或者密码错误！'];
        }
        $C->set('loginTime', $loginTime);
        $C->save();
        break;
    case 'logout': // 退出登录
        setcookie('AmoliAdmin_' . $user, '', time() - 1552294270);
        exit('<script language="javascript">alert("您已成功注销本次登陆！");window.location.href="./";</script>');
        break;
    case 'lock': // 锁屏验证
        $lockPwd = MD5($_POST['lockPwd'] . '$$Www.Amoli.Co$$');
        if ($lockPwd == $pass) {
            $result = ['code' => 1, 'msg' => '成功！'];
        } else {
            $result = ['code' => 2, 'msg' => '密码错误！'];
        }
        break;
    case 'setaccount': // 修改后台帐号密码
        $POST_user = $_POST['user'];
        $POST_pass = MD5($_POST['pass'] . '$$Www.Amoli.Co$$');
        $POST_confirmPwd = $_POST['confirmPwd'];
        if ($POST_pass != $pass) {
            $result = ['code' => 2, 'msg' => '密码错误，请重新输入！'];
        } else {
            $C->set('user', $POST_user);
            $C->set('pass', MD5($POST_confirmPwd . '$$Www.Amoli.Co$$'));
            $msg = $C->save();
            if ($msg) {
                $result = ['code' => 1, 'msg' => '修改成功！'];
            } else {
                $result = ['code' => 2, 'msg' => $msg];
            }
        }
        break;
    default:
        $result = ['code' => 2, 'msg' => 'No Act!'];
}
echo json_encode($result);
