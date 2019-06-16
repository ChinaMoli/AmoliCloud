<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/app/class/Amoli.class.php';
$act = $_GET['act'];
$dir = $_GET['dir'];
$C = new Config('Config');
$Amoli = new Amoli();
$oss = $C->get('oss');
$cos = $C->get('cos');
$type = $C->get('type', 'local');
$indexpass = md5($C->get('indexpass'));
$Cookie = $_COOKIE['Amoli_index'];
($Cookie == $indexpass || $C->get('indexpass') == '') ? $log = true : $log = false;

switch ($act) {
    case 'getConfig': // 获取配置
        file_exists('install/install.lock') ? $install = true : $install = false;
        $result = ['code' => '0', 'name' => $C->get('name'), 'version' => $C->get('version'), 'record' => $C->get('record'), 'log' => $log, 'install' => $install];
        break;

    case 'getList': // 加载目录
        if (!$log) {
            echo json_encode(['code' => '-1', 'msg' => '未登录', 'data' => null]);
            return;
        }
        switch ($type) {
            case 'local': //本地存储
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
                $list = $Amoli->getLocalList($dir);
                break;
            case 'oss': //OSS存储
                $dir = $oss['osshost'] . $dir;
                $list = $Amoli->getOssList($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $dir);
                break;
            case 'cos': //COS存储
                $dir = $cos['coshost'] . $dir;
                $list = $Amoli->getCosList($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $dir);
                break;
        }
        $result = ['code' => '0', 'msg' => '获取成功', 'data' => $list];
        break;
    case 'getUrl': // 获取文件下载Url
        $object = $Amoli->DirEncoding($_SERVER["QUERY_STRING"]);
        switch ($type) {
            case 'local':
                $url = '/' . $C->get('localhost') . $object;
                $result = ['code' => '0', 'msg' => true, 'url' => $url];
                break;
            case 'oss':
                $object = $oss['osshost'] . $object;
                $signedUrl = $Amoli->getOssUrl($oss['bucket'], $oss['endpoint'], $oss['accessKeyId'], $oss['accessKeySecret'], $oss['ossdomain'], $object);
                $result = $signedUrl;
                break;
            case 'cos':
                $object = $cos['coshost'] . $object;
                $signedUrl = $Amoli->getCosUrl($cos['bucket'], $cos['region'], $cos['secretId'], $cos['secretKey'], $object);
                $result = $signedUrl;
                break;
        }
        break;

    case 'login': // 前台登录
        $indexpass2 = md5($_POST['indexpass']);
        if ($indexpass2 == $indexpass) {
            setcookie('Amoli_index', $indexpass2, time() + 3600 * 24); // 写入Cookies
            $result = ['msg' => true];
        } else {
            $result = ['msg' => false];
        }
        break;

    case 'logout': // 退出登录
        setcookie('Amoli_index', '', time() - 1552294270);
        exit('<script language="javascript">alert("您已成功注销本次登陆！");window.location.href="index.html";</script>');
        break;

    default:
        echo 'No Act!';
}
echo json_encode($result);
