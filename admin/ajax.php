<?php
error_reporting(0); // 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once '../app/class/Amoli.class.php';
require_once '../app/sdk/autoload.php';
$C = new Config('../Config');
$Amoli = new Amoli();
$user = $C->get('user');
$pass = $C->get('pass');
$Cookie = $_COOKIE['Admin_' . $user];
$act = $_GET['act'];
$dir = $_GET['dir'];
$bucket = $C->get('bucket');
$endpoint = $C->get('endpoint');
$accessKeyId = $C->get('accessKeyId');
$accessKeySecret = $C->get('accessKeySecret');
use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;
// 判断是否登录(排除登录操作)
if (!isset($Cookie) || $Cookie != $pass) {
    if ($act != 'login') {
        echo json_encode(['code' => '-1', 'data' => null]);
        return;
    }
}
switch ($act) {
    case 'getList': // 加载目录
        if ($C->get('type') == 'oss') {
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $options = array('prefix' => $dir, 'max-keys' => '1000');
                $listObjectInfo = $ossClient->listObjects($bucket, $options);
                $prefixlist = $listObjectInfo->getPrefixList();
                $objectList = $listObjectInfo->getObjectList();
                // 判断是否根目录 （非根目录则显示返回上一层）
                if ($dir) {
                    $result[] = ['type' => 'reply', 'name' => '返回上一层', 'size' => '', 'time' => ''];
                }
                if (!empty($prefixlist)) {
                    foreach ($prefixlist as $prefixInfo) {
                        $Wjj_Name = str_replace($dir, '', $prefixInfo->getPrefix()); //替换掉多于的文件夹名 只剩当前文件夹名
                        $Wjj_Name = str_replace("/", '', $Wjj_Name); // 去掉当前文件夹名的“/”
                        $result[] = [
                            'type' => 'wjj',
                            'name' => $Wjj_Name,
                            'size' => '',
                            'time' => ''
                        ];
                    }
                }
                if (!empty($objectList)) {
                    foreach ($objectList as $objectInfo) {
                        $Wj_Name = str_replace($dir, '', $objectInfo->getKey());
                        if ($Wj_Name != '') {
                            $Wj_Size = $Amoli->getFilesize($objectInfo->getSize());
                            $Wj_Time = date("Y-m-d H:i", strtotime($objectInfo->getLastModified()));
                            $result[] = [
                                'type' => $Amoli->getStamp($Wj_Name),
                                'name' => $Wj_Name,
                                'size' => $Wj_Size,
                                'time' => $Wj_Time
                            ];
                        }
                    }
                }
                // 文件夹下没有文件输出提示
                if (empty($result)) {
                    $result[] = ['type' => "null", 'name' => "提示：当前文件夹下没有文件", 'size' => "", 'time' => ""];
                }
            } catch (OssException $e) {
                $result = ['msg' => $e->getMessage()];
            }
        } else {
            $folder = [];
            $file2 = [];
            // 判断是否根目录 （非根目录则显示返回上一层）
            if ($dir) {
                $folder[] = ['type' => 'reply', 'name' => '返回上一层', 'size' => '', 'time' => ''];
            }
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
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
            $result = array_merge($folder, $file2);
        }
        break;
    case 'Downfile': // 下载文件
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir); // 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace("+", "%2B", $dir[1]); // 替换$dir[1]里面的 “+”，后赋值给 $dir
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
                $result = ['msg' => true, 'url' => $signedUrl];
            } catch (OssException $e) {
                $result = ['msg' => $e->getMessage()];
            }
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol . $_SERVER['SERVER_NAME'] . ':' . $_SERVER["SERVER_PORT"] . '/' . $C->get('localhost') . $dir;
            $result = ['msg' => true, 'url' => $url];
        }
        break;
    case 'Delfile': // 删除文件
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir); // 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace("+", "%2B", $dir[1]); // 替换$dir[1]里面的 “+”，后赋值给 $dir
        $dir = UrlDecode($dir); // 将得到的$dir进行URL解码
        if ($C->get('type') == 'oss') {
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $ossClient->deleteObject($bucket, $dir);
                $result = ['msg' => 'ok'];
            } catch (OssException $e) {
                $result = ['msg' => $e->getMessage()];
            }
        } else {
            $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $Amoli->getEncoding($C->get('localhost') . $dir, true);
            if (unlink($file)) {
                $result = ['msg' => 'ok'];
            } else {
                $result = ['msg' => '删除失败！'];
            }
        }
        break;
    case 'ossUpload': // OSS上传文件
        function gmt_iso8601($time)
        {
            $dtStr = date("c", $time);
            $mydatetime = new DateTime($dtStr);
            $expiration = $mydatetime->format(DateTime::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration . "Z";
        }
        if (strstr($endpoint, "https://")) {
            $Bucket = str_replace('https://', 'https://' . $bucket . '.', $endpoint);
        } else {
            $Bucket = str_replace('http://', 'http://' . $bucket . '.', $endpoint);
        }
        $callbackUrl = 'https://www.amoli.co/?GET';
        $callback_param = [
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ];
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 300;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;
        $start = array(0 => 'starts-with', 1 => '$Key', 2 => $dir);
        $conditions[] = $start;
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $accessKeySecret, true));
        $result = [
            'accessid' => $accessKeyId,
            'host' => $Bucket,
            'policy' => $base64_policy,
            'signature' => $signature,
            'expire' => $end,
            'callback' => $base64_callback_body,
            'dir' => $dir
        ];
        break;
    case 'localUpload': // 本地上传文件
        $filename = $_FILES['file']['name'];
        $filesize = $_FILES['file']['size'];
        $source = $_FILES['file']['tmp_name'];
        $dir = $Amoli->getEncoding($C->get('localhost') . $dir . $filename, true);
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir;
        move_uploaded_file($source, $destination) ? $msg = '上传成功' : $msg = '上传失败';
        $result = ['msg' => $msg, 'name' => $filename, 'size' => $filesize];
        break;
    case 'systemParameter': // 系统基本参数
        $result = $C->get() + [
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
        $bucket = $_POST['bucket'];
        $endpoint = $_POST['endpoint'];
        $accessKeyId = $_POST['accessKeyId'];
        $accessKeySecret = $_POST['accessKeySecret'];
        $C->set('bucket', $bucket);
        $C->set('endpoint', $endpoint);
        $C->set('accessKeyId', $accessKeyId);
        $C->set('accessKeySecret', $accessKeySecret);
        $C->set('ossdomain', $_POST['ossdomain']);
        $C->set('indexpass', $_POST['indexpass']);
        $C->set('record', $_POST['record']);
        $msg = $C->save();
        if ($type == "oss") { // 设置跨域资源共享规则
            $corsConfig = new CorsConfig();
            $rule = new CorsRule();
            $rule->addAllowedHeader("*");
            $rule->addAllowedOrigin("*");
            $rule->addAllowedMethod("POST");
            $rule->setMaxAgeSeconds(0);
            $corsConfig->addRule($rule);
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->putBucketCors($bucket, $corsConfig);
        }
        if ($msg) {
            $result = ['msg' => '修改成功！'];
        } else {
            $result = ['msg' => $msg];
        }
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
