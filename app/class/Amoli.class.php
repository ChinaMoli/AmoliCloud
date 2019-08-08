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
     * @param $dir  判断是否为路径 
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
     * 分享文件
     * @param $data 数据
     * @return 分享文件URL
     */
    public function postShare($data)
    {
        // 取网站域名
        $url = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $url = 'https://';
        $url .= $_SERVER['HTTP_HOST'];
        // 判断是否安装在根目录
        $name = dirname(dirname($_SERVER['SCRIPT_NAME']));
        ($name == DIRECTORY_SEPARATOR) ? $dir = '' : $dir = $name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.t.sina.com.cn/short_url/shorten.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'source=3271760578&url_long=' . $url . '?'  . $data);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        $url_short = $result[0]->url_short;
        $result = str_replace('http://t.cn/', $url . $dir . '/share.php?s=', $url_short);
        return $result;
    }

    /**
     * 获取分享文件数据
     * @param $s 字符串
     * @return 分享文件内容
     */
    public function getShare($s)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.t.sina.com.cn/short_url/expand.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'source=3271760578&url_short=http://t.cn/' . $s);
        $result = json_decode(curl_exec($ch));
        if ($result->error_code) return false;
        curl_close($ch);
        $data = $result[0]->url_long;
        if (!strpos($data, $_SERVER['HTTP_HOST'])) return false;
        $data = base64_decode(substr($data, strpos($data, '?') + 1));
        $data = explode('{/}', $data);
        if (!$data[2]) return false;
        $name = $data[0];
        $wz = strripos($data[0], '/');
        if ($wz) $name = substr($data[0], $wz + 1);
        // 获取文件后戳
        switch ($this->getStamp($name)) {
            case "zip":
            case "rar":
            case "7z":
                $type = "file_zip";
                break;
            case "jpg":
            case "png":
            case "bmp":
            case "gif":
            case "ico":
                $type = "file_img";
                break;
            case "htm":
            case "html":
                $type = "file_html";
                break;
            case "php":
            case "css":
            case "jsp":
            case "js":
                $type = "file_code";
                break;
            case "exe":
                $type = "file_exe";
                break;
            case "docx":
            case "doc":
                $type = "file_word";
                break;
            case "xlsx":
            case "xls":
                $type = "file_excel";
                break;
            case "pptx":
            case "ppt":
                $type = "file_ppt";
                break;
            case "pdf":
                $type = "file_pdf";
                break;
            case "psd":
                $type = "file_psd";
                break;
            case "mp4":
                $type = "file_video";
                break;
            case "mp3":
                $type = "file_music";
                break;
            case "txt":
                $type = "file_txt";
                break;
            case "wjj":
                $type = "folder";
                break;
            case "apk":
                $type = "file_apk";
                break;
            default:
                $type = "file";
        }
        $result = [
            'name' => $name,
            'dir' => $data[0],
            'type' => $type,
            'time' => $data[1],
            'size' => $data[2]
        ];
        return $result;
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
        $encode = false;
        // 判断是否需要进行编码
        if (!json_encode($list)) $encode = true;
        foreach ($list as $file) {
            $file_location = $dir . $this->getEncoding($file, true);
            // 判断是否需要进行编码
            if ($encode) {
                $file_location = $dir . $file;
                $file = $this->getEncoding($file);
            };
            if (is_dir($file_location) && $file != '.' && $file != '..') {
                $folder[] = [
                    'type' => 'wjj',
                    'name' => $file,
                    'size' => '',
                    'time' => ''
                ];
            } elseif ($file != '.' && $file != '..') {
                $filesize = filesize($file_location);
                $filestime = date('Y-m-d H:i', filemtime($file_location));
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
                // 协议
                strstr($signedUrl, "http://") ? $protocol = 'http://' : $protocol = 'https://';
                // 文件URL
                $fileUrl = substr($signedUrl, strpos($signedUrl, '/', 10));
                // 下载地址 协议+自定义域名+文件Url
                $signedUrl = $protocol . $ossdomain . $fileUrl;
            }
            return ['code' => 1, 'msg' => '获取成功！', 'data' => ['url' => $signedUrl]];
        } catch (OssException $e) {
            return ['code' => 2, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 获取Cos下载链接
     * @param $object 对象
     * @return 下载链接
     */
    public function getCosUrl($bucket, $region, $secretId, $secretKey, $object)
    {
        $cosClient = new Qcloud\Cos\Client(array('region' => $region, 'credentials' => array('secretId'  => $secretId, 'secretKey' => $secretKey)));
        try {
            $options = array('ResponseContentType' => 'application/octet-stream');
            $signedUrl = $cosClient->getObjectUrl($bucket, $object, '+2 minutes', $options);
            return ['code' => 1, 'msg' => '获取成功！', 'data' => ['url' => $signedUrl]];
        } catch (\Exception $e) {
            return ['code' => 2, 'msg' => $e];
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
            return ['code' => 1, 'msg' => '删除成功！'];
        } catch (OssException $e) {
            return ['code' => 2, 'msg' => '错误代码：<br>' . $e->getMessage()];
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
            return ['code' => 1, 'msg' => '删除成功！'];
        } catch (\Exception $e) {
            return ['code' => 2, 'msg' => '错误代码：<br>' . $e];
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
        $rule->addAllowedHeader('*');
        $rule->addAllowedOrigin('*');
        $rule->addAllowedMethod('GET');
        $rule->addAllowedMethod('POST');
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
                    'AllowedMethods' => array('GET,POST'),
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
        $KeyTime = (string) ($time - 60) . ';' . (string) ($time + 3600);
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
            return ['code' => 1, 'msg' => '创建成功！'];
        } catch (OssException $e) {
            return ['code' => 2, 'msg' => '错误代码：<br>' . $e];
        }
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
            return ['code' => 1, 'msg' => '创建成功！'];
        } catch (\Exception $e) {
            return ['code' => 2, 'msg' => '错误代码：<br>' . $e];
        }
    }
}
