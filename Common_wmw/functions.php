<?php
	
/** 
 * 删除单个文件
 * $folder_path  文件路径
 * $file_name    文件名称
 */
function clear_file($file_path,$file_name='') {
	if($file_name != '') {
		$onestr = substr($file_path, 0, 1);
		$laststr = substr($file_path, strlen($file_path));
		
		if ($onestr != '/') {
			$file_path = "/".$file_path;
		}
		
		if ($laststr != '/') {
			$file_path = $file_path.'/';
		}
		
		if(file_exists($file_path.$file_name)) {
			unlink($file_path.$file_name); 
		}
	}
}


/**
 * todolist:临时的函数处理
 * 向当前目录下的moniter_error.log文件中追加信息
 * $anlicheng 2012-5-31
 */
function moniter_control($user, $method, $insert_nums = null) {
    exit('call me!');
    $log_filepath = WEB_ROOT_DIR . "/moniter_error.log";
    
    $err_msg = $method . "	" . $user['ams_account'] . "({$user['ams_name']})" . "	" . date("Y-m-d H:i:s", time()) . "(" . time() . ")";
    if(!empty($insert_nums)) {
        $err_msg .= "	插入数据:" . intval($insert_nums);
    }
    $err_msg .= "\n";
    
    $fp = fopen($log_filepath, 'a');
    fwrite($fp, $err_msg, strlen($err_msg));
    fclose($fp);
}


/* 
 * 根据url 获取域名 
 * 举例: url = http://vm.wmw.cn/xxx.php
 *      $domain = $this->get_domain($url)  // echo wmw.cn
 */

function get_domain($url){
    $pattern = "/[\w-]+\.(com|net|org|gov|cc|biz|info|cn)(\.(cn|hk))*/";
    preg_match($pattern, $url, $matches);
    if(count($matches) > 0) {
        return $matches[0];
    } else {
        $rs = parse_url($url);
        $main_url = $rs["host"];
        if(!strcmp(long2ip(sprintf("%u",ip2long($main_url))),$main_url)) {
            return $main_url;
        } else {
            $arr = explode(".",$main_url);
            $count=count($arr);
            $endArr = array("com","net","org","3322");//com.cn  net.cn 等情况
            if (in_array($arr[$count-2],$endArr)){
                $domain = $arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
            }else{
                $domain =  $arr[$count-2].".".$arr[$count-1];
            }
            return $domain;
        }
    }
}

/**
 * time33转换算法
 * @param $string 字符串
 * @deprecated
 * 注明：
 * 1. 该函数不确保传入的串和输出的数值之间是一一对应的映射关系；
 * 2. 主要的应用在于，利用该算法实现字符串到整数的映射将其圈定在不同的整数范围内，提交字符串索引的效率；
 */
function time33($string) {
    $string = strval($string);
    if(strlen($string) >= 30) {
        $string = substr($string, 0, 30);
    }
    
    $code = 0;
    for ($i = 0, $len = strlen($string); $i < $len; $i++) {
        $code = (int)(($code<<5) + $code + ord($string{$i})) & 0x7FFFFFFF;
    }
    
    return $code;
}



