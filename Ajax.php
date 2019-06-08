<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/app/class/Amoli.class.php';
require_once __DIR__ . '/app/sdk/autoload.php';
$act = $_GET['act'];
$dir = $_GET['dir'];
$C = new Config('Config');
$Amoli = new Amoli();
$bucket = $C->get('bucket');
$endpoint = $C->get('endpoint');
$accessKeyId = $C->get('accessKeyId');
$accessKeySecret = $C->get('accessKeySecret');
$indexpass = md5($C->get('indexpass'));
$Cookie = $_COOKIE['Amoli_index'];
($Cookie == $indexpass || $C->get('indexpass') == '') ? $log = true : $log = false;
use OSS\OssClient;
use OSS\Core\OssException;

switch ($act) {
    case 'getConfig': // 获取配置
        file_exists('install/install.lock') ? $install = true : $install = false;
        $result = ['code' => '0', 'name' => $C->get('name'), 'record' => $C->get('record'), 'log' => $log, 'install' => $install];
        break;

    case 'getList': // 加载目录
        if ($log) {
            if ($C->get('type') == 'oss') {
                //列出OSS文件及文件夹
                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $options = array(
                        'prefix' => $dir,
                        'max-keys' => '1000'
                    );
                    $listObjectInfo = $ossClient->listObjects($bucket, $options);
                    $prefixlist = $listObjectInfo->getPrefixList();
                    $objectList = $listObjectInfo->getObjectList();
                    if (!empty($prefixlist)) {
                        foreach ($prefixlist as $prefixInfo) {
                            $Wjj_Name = str_replace($dir, '', $prefixInfo->getPrefix()); //替换掉多于的文件夹名 只剩当前文件夹名
                            $Wjj_Name = str_replace("/", '', $Wjj_Name); // 去掉当前文件夹名的“/”
                            $list[] = ['type' => 'wjj', 'name' => $Wjj_Name, 'size' => '', 'time' => ''];
                        }
                    }
                    if (!empty($objectList)) {
                        foreach ($objectList as $objectInfo) {
                            $Wj_Name = str_replace($dir, '', $objectInfo->getKey());
                            if ($Wj_Name != '') {
                                $Wj_Size = $Amoli->getFilesize($objectInfo->getSize());
                                $Wj_Time = date("Y-m-d H:i", strtotime($objectInfo->getLastModified()));
                                $list[] = ['type' => $Amoli->getStamp($Wj_Name), 'name' => $Wj_Name, 'size' => $Wj_Size, 'time' => $Wj_Time];
                            }
                        }
                    }
                    // 文件夹下没有文件输出提示
                    if (empty($list)) {
                        $list[] = ['type' => 'null', 'name' => '提示：当前文件夹下没有文件', 'size' => '', 'time' => ''];
                    }
                } catch (OssException $e) {
                    echo $e->getMessage();
                }
            } else {
                $folder = [];
                $file2 = [];
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir,true);
                $list = scandir($dir);
                foreach ($list as $file) {
                    $file_location = $dir . $file;
                    $filesize = filesize($file_location);
                    $filestime = date("Y-m-d H:i", filemtime($file_location));
                    $file = $Amoli->getEncoding($file);
                    if (is_dir($file_location) && $file != '.' && $file != '..') {
                        $folder[] = [
                            'type' => 'wjj',
                            'name' => $file,
                            'size' => '',
                            'time' => ''
                        ];
                    } elseif ($file != '.' && $file != '..') {
                        $file2[] = [
                            'type' => $Amoli->getStamp($file),
                            'name' => $file,
                            'size' => $Amoli->getFilesize($filesize),
                            'time' => $filestime
                        ];
                    }
                }
                $list = array_merge($folder, $file2);
                if (empty($list)) {
                    $list[] = ['type' => 'null', 'name' => '提示：当前文件夹下没有文件', 'size' => '', 'time' => ''];
                };
            }
            $result = ['code' => '0', 'msg' => '获取成功', 'data' => $list];
        }
        break;
    case 'getUrl': // 获取文件下载Url
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir); // 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace('+', '%2B', $dir[1]); // 替换$dir[1]里面的 “+”，后赋值给 $dir
        $dir = UrlDecode($dir); // 将得到的$dir进行URL解码
        if ($C->get('type') == 'oss') {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $options = array('response-content-type' => 'application/octet-stream');
            try {
                $signedUrl = $ossClient->signUrl($bucket, $dir, 120, 'GET', $options);
                $ossdomain = $C->get('ossdomain');
                // 自定义域名
                if ($ossdomain) {
                    strstr($signedUrl, "http://") ? $protocol = 'http://' : $protocol = 'https://';
                    $url = str_replace($protocol, $protocol . $bucket . '.', $endpoint);
                    $signedUrl = str_replace($url, $protocol . $ossdomain, $signedUrl);
                }
                $result = ['code' => '0', 'msg' => true, 'url' => $signedUrl];
            } catch (OssException $e) {
                $result = ['code' => '-1', 'msg' => $e->getMessage()];
            }
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/' . $C->get('localhost') . $dir;
            $result = ['code' => '0', 'msg' => true, 'url' => $url];
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