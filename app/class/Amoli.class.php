<?php
require_once __DIR__ . '/../sdk/oss/autoload.php';
require_once __DIR__ . '/../sdk/cos/autoload.php';
use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;

/**
 * Class Config
 * 写入读取配置
 */
class Config
{
    private $data;
    private $file;

    /** 
     * 构造函数 
     * @param $file 存储数据文件 
     * @return  
     */
    function __construct($file)
    {
        $file = $file . '.php';
        $this->file = $file;
        $this->data = self::read($file);
    }

    /** 
     * 读取配置文件 
     * @param $file 要读取的数据文件 
     * @return 读取到的全部数据信息 
     */
    public function read($file)
    {
        if (!file_exists($file)) return array();
        $str = file_get_contents($file);
        $str = substr($str, strlen('<?php exit;?>'));
        $data = json_decode($str, true);
        if (is_null($data)) return array();
        return $data;
    }

    /** 
     * 获取指定项的值 
     * @param $key 要获取的项名 
     * @param $default 默认值 
     * @return data 
     */
    public function get($key = null, $default = '')
    {
        if (is_null($key)) return $this->data; //取全部数据  
        if (isset($this->data[$key])) return $this->data[$key];
        return $default;
    }

    /** 
     * 设置指定项的值 
     * @param $key 要设置的项名 
     * @param $value 值 
     * @return null 
     */
    public function set($key, $value)
    {
        if (is_string($key)) {   // 更新单条数据  
            $this->data[$key] = $value;
        } else if (is_array($key)) {   // 更新多条数据                 
            foreach ($this->data as $k => $v) {
                if ($v[$key[0]] == $key[1]) {
                    $this->data[$k][$value[0]] = $value[1];
                }
            }
        }
        return $this;
    }

    /** 
     * 删除并清空指定项 
     * @param $key 删除项名 
     * @return null 
     */
    public function delete($key)
    {
        unset($this->data[$key]);
        return $this;
    }

    /** 
     * 保存配置文件 
     * @param $file 要保存的数据文件 
     * @return true-成功 其它-保存失败原因 
     */
    public function save()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            $jsonStr = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $jsonStr = json_encode($this->data);
        }
        // 含有二进制或非utf8字符串对应检测  
        if (is_null($jsonStr)) return '数据文件有误';
        $buffer = '<?php exit;?>' . $jsonStr;
        $file_strm = fopen($this->file, 'w');
        if (!$file_strm) return '写入文件失败，请赋予 ' . $this->file . ' 文件写权限！';
        fwrite($file_strm, $buffer);
        fclose($file_strm);
        return true;
    }
}

/**
 * Class Amoli
 * 包含AmoliCloud所有常用函数
 */
class Amoli
{
    /**
     * 单位换算
     * @param $num 需要换算的数据
     * @return 换算的结果 
     */
    public function getFilesize($num)
    {
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

    /**
     * 取文件后戳
     * @param $file 需要换要取后戳的文件名
     * @return 文件的小写后戳 
     */
    public function getStamp($file)
    {
        $stamp = strrpos($file, '.') + 1; //+1为了去掉 “.”
        $stamp = substr($file, $stamp);
        return strtolower($stamp); //全部换成小写，避免前端出错
    }

    /**
     * 对WINNT服务器进行编码
     * @param $data 需要编码的数据
     * @param $dir 判断是否为路径 
     * @return 编码后数据
     */
    public function getEncoding($data, $dir = false)
    {
        if (PHP_OS != 'Linux') $dir ? $data = iconv('utf-8', 'gbk', $data) : $data = iconv('gbk', 'utf-8', $data);
        return $data;
    }

    /**
     * 换算 ISO8601 GMT 时间
     * @param $time 需要换算的时间戳
     * @return ISO8601GMT 时间
     */
    public function gmt_iso8601($time)
    {
        $dtStr = date('c', $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos) . 'Z';
        return $expiration;
    }

    /**
     * 对Get的Dir进行编码处理
     * @param $data 需要编码的数据
     * @return 编码后数据
     */
    public function DirEncoding($data)
    {
        preg_match('/dir=(.*)/i', $data, $dir); // 用正则提取 $_SERVER["QUERY_STRING"] 里的 $dir=”
        $dir = str_replace("+", "%2B", $dir[1]); // 替换$dir[1]里面的 “+”，后赋值给 $dir
        $data = UrlDecode($dir); // 将得到的$dir进行URL解码
        return $data;
    }

    /**
     * 获取本地文件列表
     * @param $ossdomain 自定义域名
     * @param $dir 文件路径
     * @return 本地文件列表
     */
    public function getLocalList($dir)
    {
        $folder = [];
        $file2 = [];
        $list = scandir($dir);
        foreach ($list as $file) {
            $file_location = $dir . $file;
            $filesize = filesize($file_location);
            $filestime = date("Y-m-d H:i", filemtime($file_location));
            $file = $this->getEncoding($file);
            if (is_dir($file_location) && $file != '.' && $file != '..') {
                $folder[] = [
                    'type' => 'wjj',
                    'name' => $file,
                    'size' => '',
                    'time' => ''
                ];
            } elseif ($file != '.' && $file != '..') {
                $file2[] = [
                    'type' => $this->getStamp($file),
                    'name' => $file,
                    'size' => $this->getFilesize($filesize),
                    'time' => $filestime
                ];
            }
        }
        $result = array_merge($folder, $file2);
        empty($result) ? $result[] = ['type' => 'null', 'name' => '提示：当前文件夹下没有文件', 'size' => '', 'time' => ''] : '';
        return $result;
    }

    /**
     * 获取OSS文件列表
     * @param $ossdomain 自定义域名
     * @param $dir 文件路径
     * @return OSS文件列表
     */
    public function getOssList($bucket, $endpoint, $accessKeyId, $accessKeySecret, $dir)
    {
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $options = array('prefix' => $dir, 'max-keys' => '1000');
            $listObjectInfo = $ossClient->listObjects($bucket, $options);
            $prefixlist = $listObjectInfo->getPrefixList();
            $objectList = $listObjectInfo->getObjectList();
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
                    if ($Wj_Name) {
                        $Wj_Size = $this->getFilesize($objectInfo->getSize());
                        $Wj_Time = date("Y-m-d H:i", strtotime($objectInfo->getLastModified()));
                        $result[] = [
                            'type' => $this->getStamp($Wj_Name),
                            'name' => $Wj_Name,
                            'size' => $Wj_Size,
                            'time' => $Wj_Time
                        ];
                    }
                }
            }
            // 文件夹下没有文件输出提示
            empty($result) ? $result[] = ['type' => 'null', 'name' => '提示：当前文件夹下没有文件', 'size' => '', 'time' => ''] : '';
        } catch (OssException $e) {
            $result = ['msg' => $e->getMessage()];
        }
        return $result;
    }

    /**
     * 获取COS文件列表
     * @param $dir 文件路径
     * @return COS文件列表
     */
    public function getCosList($bucket, $region, $secretId, $secretKey, $dir)
    {
        $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'schema' => 'https', 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
        try {
            $list = $cosClient->listObjects(array('Bucket' => $bucket, 'Delimiter' => '/', 'Prefix' => $dir, 'MaxKeys' => 1000));
            $prefixlist = $list['CommonPrefixes'];
            $objectList = $list['Contents'];
            if (!empty($prefixlist)) {
                foreach ($prefixlist as $prefixInfo) {
                    $Wjj_Name = str_replace($dir, '', $prefixInfo['Prefix']); //替换掉多于的文件夹名 只剩当前文件夹名
                    $Wjj_Name = str_replace("/", '', $Wjj_Name); // 去掉当前文件夹名的“/”
                    $result[] = ['type' => 'wjj', 'name' => str_replace($dir, '', $Wjj_Name), 'size' => '', 'time' => ''];
                }
            }
            if (!empty($objectList)) {
                foreach ($objectList as $objectInfo) {
                    $Wj_Name = str_replace($dir, '', $objectInfo['Key']);
                    if ($Wj_Name) {
                        $Wj_Size = $this->getFilesize($objectInfo['Size']);
                        $Wj_Time = date("Y-m-d H:i", strtotime($objectInfo['LastModified']));
                        $result[] = ['type' => $this->getStamp($Wj_Name), 'name' => $Wj_Name, 'size' => $Wj_Size, 'time' => $Wj_Time];
                    }
                }
            }
            empty($result) ? $result[] = ['type' => 'null', 'name' => '提示：当前文件夹下没有文件', 'size' => '', 'time' => ''] : '';
        } catch (\Exception $e) {
            $result = ['msg' => $e];
        }
        return $result;
    }

    /**
     * 获取Oss下载链接
     * @param $ossdomain 自定义域名
     * @param $object 对象
     * @return 下载链接
     */
    public function getOssUrl($bucket, $endpoint, $accessKeyId, $accessKeySecret, $ossdomain, $object)
    {
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $options = array('response-content-type' => 'application/octet-stream');
        try {
            $signedUrl = $ossClient->signUrl($bucket, $object, 120, 'GET', $options);
            // 自定义域名
            if ($ossdomain) {
                strstr($signedUrl, "http://") ? $protocol = 'http://' : $protocol = 'https://';
                $url = str_replace($protocol, $protocol . $bucket . '.', $endpoint);
                $signedUrl = str_replace($url, $protocol . $ossdomain, $signedUrl);
            }
            return ['code' => '0', 'msg' => true, 'url' => $signedUrl];
        } catch (OssException $e) {
            return ['code' => '-1', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 获取Cos下载链接
     * @param $object 对象
     * @return 下载链接
     */
    public function getCosUrl($bucket, $region, $secretId, $secretKey, $object)
    {
        $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'schema' => 'https', 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
        try {
            $options = array('ResponseContentType' => 'application/octet-stream');
            $signedUrl = $cosClient->getObjectUrl($bucket, $object, '+2 minutes', $options);
            return ['code' => '0', 'msg' => true, 'url' => $signedUrl];
        } catch (\Exception $e) {
            return ['code' => '-1', 'msg' => $e];
        }
    }

    /**
     * 删除OSS内文件
     * @param $object 对象
     * @return 下载链接
     */
    public function getOssDel($bucket, $endpoint, $accessKeyId, $accessKeySecret, $object)
    {
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->deleteObject($bucket, $object);
            return  ['msg' => 'ok'];
        } catch (OssException $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    /**
     * 删除Cos内文件
     * @param $object 对象
     * @return 下载链接
     */
    public function getCosDel($bucket, $region, $secretId, $secretKey, $object)
    {
        $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'schema' => 'https', 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
        try {
            $cosClient->deleteObject(array('Bucket' => $bucket, 'Key' => $object));
            return  ['msg' => 'ok'];
        } catch (\Exception $e) {
            return ['msg' => $e];
        }
    }

    /**
     * 设置OSS跨域规则
     * @param
     * @return
     */
    public function OssCors($bucket, $endpoint, $accessKeyId, $accessKeySecret)
    {
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

    /**
     * 设置COS跨域规则
     * @param
     * @return
     */
    public function CosCors($bucket, $region, $secretId, $secretKey)
    {
        $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'schema' => 'https', 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
        $cosClient->putBucketCors(array(
            'Bucket' => $bucket,
            'CORSRules' => array(
                array(
                    'AllowedHeaders' => array('*',),
                    'AllowedMethods' => array('POST'),
                    'AllowedOrigins' => array('*'),
                    'ExposeHeaders' => array('*'),
                    'MaxAgeSeconds' => 1
                )
            )
        ));
    }

    /**
     * OSS上传文件
     * @param $dir 上传路径
     * @return 
     */
    public function OssUpfile($bucket, $endpoint, $accessKeyId, $accessKeySecret, $dir)
    {
        strstr($endpoint, 'https://') ? $protocol = 'https://' : $protocol = 'http://';
        $host = str_replace($protocol, $protocol . $bucket . '.', $endpoint);
        $expiration = $this->gmt_iso8601(time() + 3600);
        $conditions = [
            [0 => 'starts-with', 1 => '$Key', 2 => $dir]
        ];
        $arr = ['expiration' => $expiration, 'conditions' => $conditions];
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $signature = base64_encode(hash_hmac('sha1', $base64_policy, $accessKeySecret, true));
        return [
            'dir' => $dir,
            'host' => $host,
            'accessid' => $accessKeyId,
            'policy' => $base64_policy,
            'signature' => $signature
        ];
    }

    /**
     * COS上传文件
     * @param $dir 上传路径
     * @return
     */
    public function CosUpfile($bucket, $region, $secretId, $secretKey, $dir)
    {
        function getAuthorization($SecretId, $SecretKey, $KeyTime)
        {
            // 整理参数
            $query = array();
            $headers = array();

            // 工具方法
            function getObjectKeys($obj)
            {
                $list = array_keys($obj);
                sort($list);
                return $list;
            }

            function obj2str($obj)
            {
                $list = array();
                $keyList = getObjectKeys($obj);
                $len = count($keyList);
                for ($i = 0; $i < $len; $i++) {
                    $key = $keyList[$i];
                    $val = isset($obj[$key]) ? $obj[$key] : '';
                    $key = strtolower($key);
                    $list[] = rawurlencode($key) . '=' . rawurlencode($val);
                }
                return implode('&', $list);
            }
            $qSignAlgorithm = 'sha1';
            $qAk = $SecretId;
            $qSignTime = $KeyTime;
            $qKeyTime = $KeyTime;
            $qHeaderList = strtolower(implode(';', getObjectKeys($headers)));
            $qUrlParamList = strtolower(implode(';', getObjectKeys($query)));
            $signKey = hash_hmac("sha1", $qKeyTime, $SecretKey);
            $formatString = implode("\n", array(strtolower('post'), '/', obj2str($query), obj2str($headers), ''));
            header('x-test-method', 'post');
            header('x-test-pathname', '/');
            $stringToSign = implode("\n", array('sha1', $qSignTime, sha1($formatString), ''));
            $qSignature = hash_hmac('sha1', $stringToSign, $signKey);
            $authorization = implode('&', array(
                'q-sign-algorithm=' . $qSignAlgorithm,
                'q-ak=' . $qAk,
                'q-sign-time=' . $qSignTime,
                'q-key-time=' . $qKeyTime,
                'q-header-list=' . $qHeaderList,
                'q-url-param-list=' . $qUrlParamList,
                'q-signature=' . $qSignature
            ));
            return $authorization;
        }
        $time = time();
        $host = 'https://' . $bucket . '.cos.' . $region . '.myqcloud.com';
        $expiration = $this->gmt_iso8601($time + 3600);
        $conditions = [
            [0 => 'starts-with', 1 => '$Key', 2 => $dir]
        ];
        $arr = ['expiration' => $expiration, 'conditions' => $conditions];
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $KeyTime = (string)($time - 60) . ';' . (string)($time + 3600);
        $signature = getAuthorization($secretId, $secretKey, $KeyTime);
        return [
            'dir' => $dir,
            'host' => $host,
            'policy' => $base64_policy,
            'secretId' => $secretId,
            'keytime' => $KeyTime,
            'signature' => $signature
        ];
    }

    /**
     * OSS新建目录
     * @param $dir 上传路径
     * @return 
     */
    public function OssNewFolder($bucket, $endpoint, $accessKeyId, $accessKeySecret, $dir)
    {
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->putObject($bucket, $dir, '');
            $msg = true;
        } catch (OssException $e) {
            $msg = $e;
        }
        return ['code' => '0', 'msg' => $msg];
    }

    /**
     * COS新建目录
     * @param $dir 上传路径
     * @return 
     */
    public function CosNewFolder($bucket, $region, $secretId, $secretKey, $dir)
    {
        try {
            $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'schema' => 'https', 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
            $cosClient->putObject(array(
                'Bucket' => $bucket,
                'Key' => $dir,
                'Body' => ''
            ));
            $msg = true;
        } catch (\Exception $e) {
            $msg = $e;
        }
        return ['code' => '0', 'msg' => $msg];
    }
}
