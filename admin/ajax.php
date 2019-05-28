<?php
error_reporting(0);// 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once '../app/class/Config.class.php';
require_once '../app/sdk/autoload.php';
$C = new Config('../Config');
$user=$C->get('user');
$pass=$C->get('pass');
$Cookie=$_COOKIE['Admin_'.$user];
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

switch($act){
    case 'getList':// 加载目录
        //单位换算
        function getFilesize($num){
            $p = 0;
            $format = 'B';
            if ($num > 0 && $num < 1024) {
                $p = 0;
                return number_format($num) . ' ' . $format;
            }
            if ($num >= 1024 && $num < pow(1024, 2)) {
                $p = 1;
                $format = 'KB';
            }
            if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
                $p = 2;
                $format = 'MB';
            }
            if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
                $p = 3;
                $format = 'GB';
            }
            $num /= pow(1024, $p);
            return number_format($num, 2) . ' ' . $format;
        }
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $options = array('prefix' => $dir , 'max-keys'=>'1000');
            $listObjectInfo = $ossClient->listObjects($bucket ,$options);
            $prefixlist = $listObjectInfo->getPrefixList();
            $objectList = $listObjectInfo->getObjectList();
            // 判断是否根目录 （非根目录则显示返回上一层）
            if($dir){
                $result[]=[
                    'type' => 'reply',
                    'name' => '返回上一层',
                    'size' => '',
                    'time' => ''
                ];
            }
            if(!empty($prefixlist)){
                foreach ($prefixlist as $prefixInfo) {
                    $Wjj_Name=str_replace($dir, '',$prefixInfo->getPrefix()); //替换掉多于的文件夹名 只剩当前文件夹名
                    $Wjj_Name=str_replace ("/",'',$Wjj_Name);// 去掉当前文件夹名的“/”
                    $result[]=[
                        'type' => 'wjj',
                        'name' => $Wjj_Name,
                        'size' => '',
                        'time' => ''
                    ];
                }
            }
            if(!empty($objectList)){
                foreach ($objectList as $objectInfo) {
                    $Wj_Name=str_replace( $dir,'',$objectInfo->getKey());
                    if($Wj_Name !=''){
                        $Wj_Size=getFilesize($objectInfo->getSize());
                        $Wj_Time=date("Y-m-d H:i",strtotime($objectInfo->getLastModified()));
                        //获取文件后戳名
                        $icon=strrpos($Wj_Name , '.')+1; // +1 为了去掉 “.”
                        $icon=substr($Wj_Name, $icon);
                        $icon=strtolower($icon); // 全部换成小写，避免前端出错
                        $result[]=[
                            'type' => $icon,
                            'name' => $Wj_Name,
                            'size' => $Wj_Size,
                            'time' => $Wj_Time
                        ];
                    }
                }
            }
            // 文件夹下没有文件输出提示
            if(empty($result)){
                $result[]=['type' =>"null",'name' =>"提示：当前文件夹下没有文件",'size' =>"",'time' =>""];
            }
        } catch (OssException $e) {
            $result = ['msg'=>$e->getMessage()];
        }
        break;
    case 'Downfile':// 下载文件
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir);// 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace("+","%2B",$dir[1]);// 替换$dir[1]里面的 “+”，后赋值给 $dir
        $dir = UrlDecode($dir);// 将得到的$dir进行URL解码
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $options = array('response-content-type' => 'application/octet-stream');
        try{
            $signedUrl = $ossClient->signUrl($bucket,$dir,120,'GET',$options);
            $ossdomain = $C->get('ossdomain');
            // 自定义域名
            if($ossdomain){
                if(strstr($signedUrl,"http://")){
                    $http = 'http://';
                }else{
                    $http = 'https://';
                }
                $url = str_replace($http,$http.$bucket.'.',$endpoint);
                $signedUrl = str_replace($url,$http.$ossdomain,$signedUrl);
            }
            $result=['msg' => 'ok','url' => $signedUrl];
        } catch (OssException $e) {
            $result=['msg' => $e->getMessage()];
        }
        break;
    case 'Delfile':// 删除文件
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir);// 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace("+","%2B",$dir[1]);// 替换$dir[1]里面的 “+”，后赋值给 $dir
        $dir = UrlDecode($dir);// 将得到的$dir进行URL解码
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->deleteObject($bucket, $dir);
            $result=['msg' => 'ok'];
        } catch(OssException $e) {
            $result=['msg' => $e->getMessage()];
        }
        break;
    case 'ossUpload':// OSS上传文件
        function gmt_iso8601($time) {
            $dtStr = date("c", $time);
            $mydatetime = new DateTime($dtStr);
            $expiration = $mydatetime->format(DateTime::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration."Z";
        }
        if(strstr($endpoint,"https://")){
            $Bucket = str_replace('https://','https://'.$bucket.'.',$endpoint);
        }else{
            $Bucket = str_replace('http://','http://'.$bucket.'.',$endpoint);
        }
        $callbackUrl = 'https://www.amoli.co/?GET';
        $callback_param = [
            'callbackUrl'=>$callbackUrl, 
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}', 
            'callbackBodyType'=>"application/x-www-form-urlencoded"
        ];
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 300;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition; 
        $start = array(0=>'starts-with', 1=>'$Key', 2=>$dir);
        $conditions[] = $start; 
        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $accessKeySecret, true));
        $result = [
            'accessid'=>$accessKeyId,
            'host'=>$Bucket,
            'policy'=>$base64_policy,
            'signature'=>$signature,
            'expire'=>$end,
            'callback'=>$base64_callback_body,
            'dir'=>$dir
        ];
        /*
        echo json_encode($response);
        exit();
        */
        break;
    case 'systemParameter': // 系统基本参数
        $result=[
            'name' => $C->get('name'),
            'user' => $C->get('user'),
            'indexpass' => $C->get('indexpass'),
            'record' => $C->get('record'),
            'version' => $C->get('version'),
            'bucket' => $C->get('bucket'),
            'endpoint' => $C->get('endpoint'),
            'accessKeyId' => $C->get('accessKeyId'),
            'accessKeySecret' => $C->get('accessKeySecret'),
            'ossdomain' => $C->get('ossdomain'),
            'server' => PHP_OS,
            'host' => $_SERVER['HTTP_HOST'],
            'root' => $_SERVER['DOCUMENT_ROOT'],
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'php_version' => PHP_VERSION,
            'upload_max' => get_cfg_var("upload_max_filesize"),
            'loginTime' => $C->get('loginTime')
        ];
        break;
    case 'webconfig': // 网站配置
        $C->set('name',$_POST['name']);
        $C->set('indexpass',$_POST['indexpass']);
        $C->set('record',$_POST['record']);
        $msg=$C->save();
        if($msg){
            $result=['msg' => '修改成功！'];
        }else{
            $result=['msg' => $msg];
        }
        break;
    case 'ossconfig': // OSS配置
        $bucket = $_POST['bucket'];
        $endpoint = $_POST['endpoint'];
        $accessKeyId = $_POST['accessKeyId'];
        $accessKeySecret = $_POST['accessKeySecret'];
        $C->set('bucket',$bucket);
        $C->set('endpoint',$endpoint);
        $C->set('accessKeyId',$accessKeyId);
        $C->set('accessKeySecret',$accessKeySecret);
        $C->set('ossdomain',$_POST['ossdomain']);
        $msg=$C->save();
        // 设置跨域资源共享规则
        $corsConfig = new CorsConfig();
        $rule = new CorsRule();
        $rule->addAllowedHeader("*");
        $rule->addAllowedOrigin("*");
        $rule->addAllowedMethod("POST");
        $rule->setMaxAgeSeconds(0);
        $corsConfig->addRule($rule);
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $ossClient->putBucketCors($bucket, $corsConfig);
        if($msg){
            $result=['msg' => '修改成功！'];
        }else{
            $result=['msg' => $msg];
        }
        break;
    case 'login': // 登录后台
        $POST_user=$_POST['user'];
        $POST_pass=MD5($_POST['pass'].'$$Www.Amoli.Co$$');
        $loginTime=date('Y-m-d H:i:s');
        if($POST_user==$user && $POST_pass==$pass){
            setcookie('Admin_' . $POST_user, $POST_pass, time()+3600*24);// 写入Cookies
            $result=['msg' => '登录成功'];
        }else{
            $result=['msg' => '帐号或者密码错误'];
        }
        $C->set('loginTime',$loginTime);
        $C->save();
        break;
    case 'logout': // 退出登录
        setcookie('Admin_'.$user,'',time()-1552294270);
        exit('<script language="javascript">alert("您已成功注销本次登陆！");window.location.href="login.html";</script>');
        $result=['msg' => '成功退出登录'];
        break;
    case 'lock': // 锁屏验证
        $lockPwd=MD5($_POST['lockPwd'].'$$Www.Amoli.Co$$');
        if($lockPwd==$pass){
            $result=['msg' => 'ok'];
        }else{
            $result=['msg' => 'no'];
        }
        break;
    case 'setaccount': // 修改后台帐号密码
        $POST_user=$_POST['user'];
        $POST_pass=MD5($_POST['pass'].'$$Www.Amoli.Co$$');
        $POST_confirmPwd=$_POST['confirmPwd'];
        if($POST_pass!=$pass){
            $result=['msg' => '密码错误，请重新输入！'];
        }else{
            $C->set('user',$POST_user);
            $C->set('pass',MD5($POST_confirmPwd.'$$Www.Amoli.Co$$'));
            $msg=$C->save();
            if($msg){
                $result=['msg' => '修改成功！'];
            }else{
                $result=['msg' => $msg];
            }
            
            
        }
        break;
    default:
        $result=['msg' => 'No Act!'];
}
// 判断是否登录(排除登录操作)
$result=['code'=>'0','data'=>$result];
if($act!="login"){
    if(!isset($Cookie) || $Cookie != $pass){
        $result=['code'=>'-1','data'=>null];
    }
}
echo json_encode($result);