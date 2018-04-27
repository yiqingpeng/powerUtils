<?php
namespace vender\Utils;

class PowerUtils
{
	
	/**
	 * 隐藏手机号中间N位
	 */
	public static function mask_mobile($mobile, $count=4){
		$start = ceil((11-$count)/2);
		$end = 11 - $start - $count;
		return preg_replace( sprintf('/^(\d{%d})\d{$d}(\d{%d})$/', $start, $count, $end), '\\1****\\2', $mobile);
	}
    
    /**
     * 获取客户端IP
     * @return string IP地址
     */
    public static function get_client_ip(){
        $ip = NULL;
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){            
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if(false !== $pos) unset($arr[$post]);
            $ip = trim($arr[0]);
        }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(isset($_SERVER['REMOTE_ADDR'])){
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        //IP地址合法性验证
        $ip = (false !== ip2long($ip))?$ip : '0.0.0.0';
        return $ip;
    }
    
    /**
     * 获取完全的请求URL
     * @return string url
     */
     public static function get_url(){
        $url = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $url .= 's';
        }
        $url .= '://';
        if ($_SERVER['SERVER_PORT'] != '80') {
            $url .= $_SERVER['HTTP_HOST'].':'. $_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }
        return $url;
    }
    
    /**
     * 获取当前站点的访问路径根目录
     * @return string 
     */
    public static function get_siteurl(){
        $uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
        return 'http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1);
    }
	
    
    /**
     * 字符串截取,支持中文和其它编码
     * @param string $str 字符串
     * @param integer $start 起始位置，从0开始计算
     * @param integer $length 截取长度
     * @param string $charset 字符串编码
     * @param boolean $suffix 是否带省略号
     * @return string 处理之后的字符串
     */
    public static function msubstr($str, $start=0, $length=15, $charset='utf-8', $suffix=true){
        if(function_exists('mb_substr')){
            return mb_substr($str, $start, $length, $charset);
        }else if(function_exists('iconv_substr')){
            return iconv_substr($str, $start, $length, $charset);
        }
        $reg['utf-8'] = '/[\x01-\x7f][\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
        $reg['gb2312'] = '/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/';
        $reg['gbk'] = '/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/';
        $reg['big5'] = '/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|[\xa1-\xfe])/';
        preg_match_all($reg[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        if($suffix){
            return $slice.'...';
        }
        return $slice;
    }

    /**
     * 产生随机字符串
     * @param type $length
     * @return type
     */
    public static function create_randomstr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    public static function get_signature($arrdata, $method = "sha1") {
        if (!function_exists($method)) {
            return false;
        }
        ksort($arrdata);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            if (strlen($paramstring) == 0) {
                $paramstring .= $key . "=" . $value;
            } else {
                $paramstring .= "&" . $key . "=" . $value;
            }
        }
        $Sign = $method($paramstring);
        return $Sign;
    }
    
    /**
     * 导出数据为excel或者csv
     * @param string $filename 导出文件名
     * @param array $data 二维数组格式的数据
     * @param array $header 标题栏
     * @param string $format csv或者xls（默认为xls）
     * @param boolean $seqColumn 是否加上序号列
     */
    public static function export_excel($filename, $data, $header = null, $seqColumn=true) {
        if(stripos($filename,'.')!==false){
            $format = substr($filename,-1*stripos($filename,'.'));
            $basename = substr($filename, 0, strrpos($filename, '.'));
        }else{
            $format = 'xls';
            $basename = $filename;
        }
        if(!in_array($format, array('xls', 'csv'))) {
            $format = 'xls';
            $basename = $filename;
        }
        
        if($format==='csv'){
            header("Content-type:text/csv;");
            header("Content-Disposition:attachment;filename=" . $basename);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $csv_data = '';
            if(!empty($header)){
                $csv_data .= implode(',',$header)."\r\n";
            }
            $i=0;
            foreach($data as $item){
                $i++;
                if($seqColumn) $csv_data .= $i.',';
                foreach($item as $field){
                    $csv_data .= '="'.str_replace('"','""',stripslashes($field)).'",';
                }
                $csv_data = rtrim($csv_data, ',');
                $csv_data .= "\r\n";
            }
            $csv_data = mb_convert_encoding($csv_data, "cp936", "UTF-8");
            
            echo $csv_data;
        }else{
            header("Content-type:application/octet-stream");
            header("Accept-Ranges:bytes");
    		header("Content-Type: application/vnd.ms-execl");   
            header("Content-Disposition: attachment; filename=$basename");   
            header("Pragma: no-cache");   
            header("Expires: 0");
            $csv_data = '';
            if(!empty($header)){
                $csv_data .= implode("\t",$header)."\r\n";
            }
            $i=0;
            foreach($data as $item){
                $i++;
                if($seqColumn) $csv_data .= $i."\t";
                foreach($item as $field){
                    $csv_data .= '="'.str_replace('"','""',stripslashes($field))."\"\t";
                }
                $csv_data = rtrim($csv_data, "\t");
                $csv_data .= "\r\n";
            }
            echo $csv_data;
        }
    }
    
    /**
     * 校验密码字符串合法性, 参考自php.net之ctype_alnum
     * @param string $password 密码字符串
     * @param array $specialChars 密码中的特殊字符（除了字母和数字之外）
     */
    public static function valid_password($password, $specialChars=null){
        if(function_exists('ctype_alnum')){
            return ctype_alnum(str_replace($specialChars, '', $password));
        }else{
            return preg_match('/^[a-zA-Z0-9]+$/', str_replace($specialChars, '', $password));
        }      
    }
    
    /**
     * 压缩php源码， 来自php.net
     * @param string $src 源码文件或者源码内容
     */
    public static function compress_php_src($src) {
        // Whitespaces left and right from this signs can be ignored
        static $IW = array(
            T_CONCAT_EQUAL,             // .=
            T_DOUBLE_ARROW,             // =>
            T_BOOLEAN_AND,              // &&
            T_BOOLEAN_OR,               // ||
            T_IS_EQUAL,                 // ==
            T_IS_NOT_EQUAL,             // != or <>
            T_IS_SMALLER_OR_EQUAL,      // <=
            T_IS_GREATER_OR_EQUAL,      // >=
            T_INC,                      // ++
            T_DEC,                      // --
            T_PLUS_EQUAL,               // +=
            T_MINUS_EQUAL,              // -=
            T_MUL_EQUAL,                // *=
            T_DIV_EQUAL,                // /=
            T_IS_IDENTICAL,             // ===
            T_IS_NOT_IDENTICAL,         // !==
            T_DOUBLE_COLON,             // ::
            T_PAAMAYIM_NEKUDOTAYIM,     // ::
            T_OBJECT_OPERATOR,          // ->
            T_DOLLAR_OPEN_CURLY_BRACES, // ${
            T_AND_EQUAL,                // &=
            T_MOD_EQUAL,                // %=
            T_XOR_EQUAL,                // ^=
            T_OR_EQUAL,                 // |=
            T_SL,                       // <<
            T_SR,                       // >>
            T_SL_EQUAL,                 // <<=
            T_SR_EQUAL,                 // >>=
        );
        if(is_file($src)) {
            if(!$src = file_get_contents($src)) {
                return false;
            }
        }
        $tokens = token_get_all($src);
        
        $new = "";
        $c = sizeof($tokens);
        $iw = false; // ignore whitespace
        $ih = false; // in HEREDOC
        $ls = "";    // last sign
        $ot = null;  // open tag
        for($i = 0; $i < $c; $i++) {
            $token = $tokens[$i];
            if(is_array($token)) {
                list($tn, $ts) = $token; // tokens: number, string, line
                $tname = token_name($tn);
                if($tn == T_INLINE_HTML) {
                    $new .= $ts;
                    $iw = false;
                } else {
                    if($tn == T_OPEN_TAG) {
                        if(strpos($ts, " ") || strpos($ts, "\n") || strpos($ts, "\t") || strpos($ts, "\r")) {
                            $ts = rtrim($ts);
                        }
                        $ts .= " ";
                        $new .= $ts;
                        $ot = T_OPEN_TAG;
                        $iw = true;
                    } elseif($tn == T_OPEN_TAG_WITH_ECHO) {
                        $new .= $ts;
                        $ot = T_OPEN_TAG_WITH_ECHO;
                        $iw = true;
                    } elseif($tn == T_CLOSE_TAG) {
                        if($ot == T_OPEN_TAG_WITH_ECHO) {
                            $new = rtrim($new, "; ");
                        } else {
                            $ts = " ".$ts;
                        }
                        $new .= $ts;
                        $ot = null;
                        $iw = false;
                    } elseif(in_array($tn, $IW)) {
                        $new .= $ts;
                        $iw = true;
                    } elseif($tn == T_CONSTANT_ENCAPSED_STRING
                           || $tn == T_ENCAPSED_AND_WHITESPACE)
                    {
                        if($ts[0] == '"') {
                            $ts = addcslashes($ts, "\n\t\r");
                        }
                        $new .= $ts;
                        $iw = true;
                    } elseif($tn == T_WHITESPACE) {
                        $nt = @$tokens[$i+1];
                        if(!$iw && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) {
                            $new .= " ";
                        }
                        $iw = false;
                    } elseif($tn == T_START_HEREDOC) {
                        $new .= "<<<S\n";
                        $iw = false;
                        $ih = true; // in HEREDOC
                    } elseif($tn == T_END_HEREDOC) {
                        $new .= "S;";
                        $iw = true;
                        $ih = false; // in HEREDOC
                        for($j = $i+1; $j < $c; $j++) {
                            if(is_string($tokens[$j]) && $tokens[$j] == ";") {
                                $i = $j;
                                break;
                            } else if($tokens[$j][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                    } elseif($tn == T_COMMENT || $tn == T_DOC_COMMENT) {
                        $iw = true;
                    } else {
                        if(!$ih) {
                            $ts = strtolower($ts);
                        }
                        $new .= $ts;
                        $iw = false;
                    }
                }
                $ls = "";
            } else {
                if(($token != ";" && $token != ":") || $ls != $token) {
                    $new .= $token;
                    $ls = $token;
                }
                $iw = true;
            }
        }
        return $new;
    }
    
    /**
     * 区分大小写的文件存在判断, 来自Thinkphp
     * 据说：
     * is_file 在文件存在的情况速度是file_exists的N倍；文件不存在的情况下，比file_exists要慢些。
     * 另外，file_exists不仅可以用来判断文件是否存在，还可以判断目录。
     * @param string $filename 文件地址
     * @return boolean
     */
    public static function file_exists_case($filename) {
        if (is_file($filename)) {
            if (strstr(PHP_OS, 'WIN')) {
                if (basename(realpath($filename)) != basename($filename))
                    return false;
            }
            return true;
        }
        return false;
    }
    
    /**
     * 判断是否SSL协议
     * @return boolean
     */
    public static function is_ssl() {
        if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
            return true;
        }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }
        return false;
    }
    
    /**
     * URL重定向 来自Thinkphp
     * @param string $url 重定向的URL地址
     * @param integer $time 重定向的等待时间（秒）
     * @param string $msg 重定向前的提示信息
     * @return void
     */
    public static function redirect($url, $time=0, $msg='') {
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str  = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }
    
    /**
     * 不区分大小写的in_array实现
     */
    public static function in_array_case($value,$array){
        return in_array(strtolower($value),array_map('strtolower',$array));
    }
    
    /**
     *对数据库数据排序
     *@param $data array 需要排序的数据，形如：[['id'=>1, 'score'=>3],...]
     *@param $orderby string 排序规则，形如："id desc, score asc"
     */
    function sort_with_orderby(array &$data, $orderby=''){
    	if(empty($orderby)) return true;
    	$sort = ['asc' => SORT_ASC, 'desc' => SORT_DESC];
    	$order = explode(',', $orderby);
    	array_walk($order, function(&$item, $key){ $item = explode(' ', trim(preg_replace('/\s+/',' ', $item)));});
    	$params = [];
    	foreach($order as $value){
    		$arr = array_column($data, $value[0]);
    		if(empty($arr)) continue;
    		$params[] = $arr;
    		$params[] = isset($value[1])?$sort[strtolower($value[1])]:$sort['asc'];
    		unset($arr);
    	}
    	$params[] = &$data;
    	return call_user_func_array('array_multisort', $params);
    }

}
