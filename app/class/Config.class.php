<?php
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
     * 构造函数 
     * @param $file 存储数据文件 
     * @return  
     */
    function __construct()
    {
        $this->C = new Config('Config');
    }

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

    public function aa()
    {
        echo $this->C->get('name');
    }
}
