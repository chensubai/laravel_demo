<?php

/**
 * 判断是否为不可操作id
 *
 * @param	number	$id	参数id
 * @param	string	$configName	配置名
 * @param	bool  $emptyRetValue
 * @param	string	$split 分隔符
 * @return	bool
 */
if (!function_exists('is_config_id')) {
    function is_config_id($id, $configName, $emptyRetValue = false, $split = ",")
    {
        if (empty($configName)) return $emptyRetValue;
        $str = trim(config($configName, ""));
        if (empty($str)) return $emptyRetValue;
        $ids = explode($split, $str);
        return in_array($id, $ids);
    }
}

/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function encrypt_password($password, $encrypt = '')
{
    $pwd = array();
    $pwd['encrypt'] = $encrypt ? $encrypt : genRandomString();
    $pwd['password'] = md5(trim($password) . $pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}


/*
@desc：php密码加盐函数
@param pass 原密码
@param salt 盐
@return ret 加密后的密码
*/
function passsalt($pass,$salt='the1App2020'){
    $str1 = mb_substr($pass,0,5);
    $str2 = mb_substr($salt,0,2);
    $str3 = mb_substr($salt,-2);
    $str4 = mb_substr($pass,-5);
    $ret = base64_encode($str1.$str2.$pass.$str3.$str4);
    return $ret;
}

/**
 * 产生一个指定长度的随机字符串,并返回给用户
 * @param type $len 产生字符串的长度
 * @return string 随机字符串
 */
function genRandomString($len = 6)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9",
    );
    $charsLen = count($chars) - 1;
    // 将数组打乱
    shuffle($chars);
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * App生成随机昵称
 * @param  integer $length [description]
 * @return [type]          [description]
 */
function generate_username( $length = 10 ) {
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ( $i = 0; $i < $length; $i++ )
    {
        // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return 'user_'.$password;
}

/**
 * 反序列化
 * @param  [type] $serial_str [description]
 * @return [type]             [description]
 */
function mb_unserialize($str) {
    return preg_replace_callback('#s:(\d+):"(.*?)";#s',function($match){return 's:'.strlen($match[2]).':"'.$match[2].'";';},$str);
}

/**
 * 随机生成鉴定编号
 * @return [type] [description]
 */
function create_ident_no() {
    $ident_no = date('Ymd').substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(1000, 9999));
    return $ident_no;
}

/**
 * 日志信息写入 json格式日志
 *
 * @param array  $arr    要写入的日志内容
 * @param string $file   日志文件名
 * @param bool   $append 是否始终写入同一个文件
 *
 * @return bool|int
 */
function log_arr(array $arr = [], $file = 'history', $append = false)
{
    if (empty($arr)) return;
    /*回溯跟踪*/
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $time = date('Y-m-d H:i:s');
    $class = isset($backtrace[1]['class']) ? $backtrace[1]['class'] : '';
    $type = isset($backtrace[1]['type']) ? $backtrace[1]['type'] : '';
    $fun = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : '';
    $traceId = request()->header('X-Trace-Id', '-');
    $ms = '-';
    if (PHP_SAPI !== 'cli') {
        /*请求处理时间计算*/;
        $ms = sprintf('%.2f', (microtime(true) - LARAVEL_START) * 1000);
        $ms = $ms > 1000 ? sprintf('%.2fs', $ms / 1000) : $ms . 'ms';
    }
    $str = sprintf('%s [%s] [%s] [%s::%s::%s]', "[{$time}]", $traceId, $ms, "{$backtrace[0]['file']}", "{$class}{$type}{$fun}", "{$backtrace[0]['line']}");

    /*日志正文*/
    foreach ($arr as $k => $v) {
        $k = is_string($k) ? " {$k}:" : ' ';
        if (is_array($v) || is_object($v)) {
            $str .= $k . json_encode($v, JSON_UNESCAPED_UNICODE);
            continue;
        }
        $v = str_replace(["\n", "\r"], ['\n', '\r'], $v);
        $str .= $k . $v;
    }
    unset($arr);
    /*日志保存地址*/
    if ($append === true) {
        $file = "{$file}.log";
    } else {
        $file = date('Ymd') . "_{$file}.log";
    }
    $path = storage_path() . '/logs/' . date('Ym');
    mkdirs($path);
    return file_put_contents("{$path}/{$file}", "{$str}\r\n", FILE_APPEND);
}

/**
 * 目录递归创建
 *
 * @param $path
 *
 * @return bool
 */
function mkdirs($path)
{
    if (is_dir($path)) return true; //已经是目录了就不用创建
    if (is_dir(dirname($path)))  return mkdir($path, 0777);  //父目录已经存在，直接创建

    mkdirs(dirname($path));     //从子目录往上创建
    return mkdir($path, 0777);  //因为有父目录，所以可以创建路径
}

/**
 * 获取对应语言
 */
function __lang($lang) {

    $sysLang = 'jp';
    if ($lang == 'en') $sysLang = 'en';
    if ($lang == 'jp') $sysLang = 'jp';
    return $sysLang;
}


function get_size($file_path)
{
    return Storage::size($file_path);
}



/**
 * 获取上传资源的CDN的地址
 * @param string  $url    资源相对地址
 * @param boolean $domain 是否显示域名 或者直接传入域名
 * @return string
 */
function cdnurl($url, $domain = false)
{
    $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
    $cdnurl  = \URL::full();
    $cdnurl = parse_url($cdnurl);
    $cdnurl = 'http://'.$cdnurl['host'];
    $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
    if ($domain && !preg_match($regex, $url)) {
        $domain = is_bool($domain) ? request()->domain() : $domain;
        $url = $domain . $url;
    }
    return $url;
}

   /**
     * 引用形式
     *
     * @param [type] $arr
     * @return void
     */
function getTreeArray($arr,$parent_id = 'parent_id'){
    $items = array();
    $tree  = array();
    foreach($arr as $key => $value){
        $items[$value['id']]=$value;
    }

    foreach($items as $k => $v){
        if(isset($items[$v[$parent_id ]])){
            $items[$v[$parent_id ]]['children'][] = &$items[$k];
        }else{
            $tree[] = &$items[$k];
        }
    }
    return $tree;
}

function objectToArray($object) {
    //先编码成json字符串，再解码成数组
    return json_decode(json_encode($object), true);
}
//获取ip
function get_ip()
{
    static $ip = '';
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
        $ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

/**
 * 生成pdf文档
 * @param integer $orderid  [description]
 * @param string  $userid   [description]
 * @param string  $filename [description]
 */
function GeneratePdf($orderid = 0,$userid='',$filename='')
{
    if(!$orderid){
        return ['code'=>0,'msg'=>'缺少参数'];
    }
    if(!$filename){
        $filename = md5(time().rand(0000000,9999999999)) .'.pdf';
    }
    $path = app_path() . 'public' . DIRECTORY_SEPARATOR . 'tablepdf' . DIRECTORY_SEPARATOR . date('Ymd');
    if(!file_exists($path)){
        mkdir($path,0777,true);
    }
    $pdfpath = $path . DIRECTORY_SEPARATOR . $filename;
    //请求协议
    $protocol_name = get_request_scheme();

    $html_url = $protocol_name.$_SERVER['HTTP_HOST'].'/member/content/html2Pdf/orderid/'.$orderid.'/userid/'.$userid;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {  //判断是linux 还是windows 系统
        $pdf_tool = app_path() .'\extend\wkhtmltopdf\bin\wkhtmltopdf';
        exec("$pdf_tool $html_url $pdfpath");
    }elseif(strtoupper(PHP_OS) === 'DARWIN'){

        shell_exec("/usr/local/bin/wkhtmltopdf $html_url ".$pdfpath);
    }else{
        shell_exec("/usr/bin/wkhtmltopdf $html_url ".$pdfpath);
    }
    if(file_exists("$pdfpath")){
        $pdfpath = strstr($pdfpath, '/tablepdf/');
        return ['code'=>1,'src'=>$pdfpath];
    }else{
        return ['code'=>0,'msg'=>'pdf文件未生成'];
    }
}
/**
 * 获取当前请求协议
 */
function get_request_scheme()
{
    return ((int)$_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http') . '://';
}





