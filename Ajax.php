<?php
error_reporting(0);// 关闭错误提示
date_default_timezone_set('Asia/Shanghai');
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/app/class/Config.class.php';
require_once __DIR__ . '/app/sdk/autoload.php';
$act = $_GET['act'];
$dir = $_GET['dir'];
$C = new Config('Config');
$bucket = $C->get('bucket');
$endpoint = $C->get('endpoint');
$accessKeyId = $C->get('accessKeyId');
$accessKeySecret = $C->get('accessKeySecret');
$indexpass = md5($C->get('indexpass'));
$Cookie=$_COOKIE['Amoli_index'];
if($Cookie == $indexpass || $C->get('indexpass') == ''){
    $log=true;
}else{
    $log=false;
}
use OSS\OssClient;
use OSS\Core\OssException;

switch ($act) {
    case 'getConfig': // 获取配置
        $result=['code' => '0','name' =>$C->get('name'),'record'=>$C->get('record'),'log'=>$log];
        echo json_encode($result);
        break;
    case 'getList':// 加载目录
        //单位换算
        if($log){
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
                if(!empty($prefixlist)){
                    foreach ($prefixlist as $prefixInfo) {
                        $Wjj_Name=str_replace($dir, '',$prefixInfo->getPrefix()); //替换掉多于的文件夹名 只剩当前文件夹名
                        $Wjj_Name=str_replace ("/",'',$Wjj_Name);// 去掉当前文件夹名的“/”
                        $list[]=[
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
                            $list[]=[
                                'type' => $icon,
                                'name' => $Wj_Name,
                                'size' => $Wj_Size,
                                'time' => $Wj_Time
                            ];
                        }
                    }
                }
                // 文件夹下没有文件输出提示
                if(empty($list)){
                    $list[]=['type' =>"null",'name' =>"提示：当前文件夹下没有文件",'size' =>"",'time' =>""];
                }
                $result=['code' => '0','msg' =>'获取成功','data' => $list];
                echo json_encode($result);
            } catch (OssException $e) {
                echo $e->getMessage();
            }
        }

        break;


    case 'getUrl':// 获取文件下载Url
        preg_match('/dir=(.*)/i', $_SERVER["QUERY_STRING"], $dir);// 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace('+','%2B',$dir[1]);// 替换$dir[1]里面的 “+”，后赋值给 $dir
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
            echo $signedUrl;
        } catch (OssException $e) {
            echo $e->getMessage();
        }
        break;
    case 'login':// 前台登录
        $indexpass2=md5($_POST['indexpass']);
        if($indexpass2==$indexpass){
            setcookie('Amoli_index', $indexpass2, time()+3600*24);// 写入Cookies
            $result=['msg' => true];
        }else{
            $result=['msg' => false];
        }
        echo json_encode($result);
        break;
    case 'logout': // 退出登录
        setcookie('Amoli_index','',time()-1552294270);
        exit('<script language="javascript">alert("您已成功注销本次登陆！");window.location.href="index.html";</script>');
        break;
    default:
        echo 'No Act!';
}