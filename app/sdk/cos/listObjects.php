<?php

require dirname(__FILE__) . '/autoload.php';

$secretId = 'AKIDlOrApJwiePzVqtwS0LVUL0V6Wpw8L5qV'; //"云 API 密钥 SecretId";
$secretKey = 'ufFdmqjQv33sVx0sRtQlz2uHXdXL4d0B'; //"云 API 密钥 SecretKey";
$bucket = 'amoli-1259320365';
$region = 'ap-chengdu'; //设置一个默认的存储桶地域
$cosClient = new Qcloud\Cos\Client(
    array(
        'region' => $region,
        'schema' => 'https', //协议头部，默认为http
        'credentials' => array(
            'secretId'  => $secretId,
            'secretKey' => $secretKey
        )
    )
);
$dir = '测试/aaa/';
try {
    $result = $cosClient->listObjects(array(
        'Bucket' => $bucket, //格式：BucketName-APPID
        'Delimiter' => '/',
        'Prefix' => $dir,
        'MaxKeys' => 1000,
    ));
    // 请求成功
    $prefixlist = $result['CommonPrefixes'];
    $objectList = $result['Contents'];
    if (!empty($prefixlist)) {
        foreach ($prefixlist as $prefixInfo) {
            $list[] = [
                'type' => 'wjj',
                'name' => str_replace($dir, '', $prefixInfo['Prefix']),
                'size' => '',
                'time' => ''
            ];
        }
    }
    if (!empty($objectList)) {
        foreach ($objectList as $objectInfo) {
            $list[] = [
                'type' => 'wj',
                'name' => str_replace($dir, '', $objectInfo['Key']),
                'size' => $objectInfo['Size'],
                'time' => $objectInfo['LastModified'],
            ];
        }
    }
    echo json_encode($list);
} catch (\Exception $e) {
    // 请求失败
    echo ($e);
}
try {    
    $key = 'blog.png';
    $options = array('ResponseContentType' => 'application/octet-stream');
    $signedUrl = $cosClient->getObjectUrl($bucket, $key, '+10 Second',$options);
    // 请求成功
    //echo $signedUrl;
} catch (\Exception $e) {
    // 请求失败
    print_r($e);
}