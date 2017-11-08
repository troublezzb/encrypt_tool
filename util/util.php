<?php
/**
 * utiliy各种常用、实用工具函数
 * ================================================================
 * ================================================================
 */

/**
 * 远程获取数据，POST模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $para 请求的数据
 * @param $input_charset 编码格式。默认值：空值
 * @return 远程输出的数据
 */
function doPost($url, $para, $input_charset = '') {
    if (trim($input_charset) != '') {
        $url = $url."_input_charset=".$input_charset;
    }
    $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
//        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_POST,true); // post传输数据
    curl_setopt($curl, CURLOPT_POSTFIELDS,$para);// post传输数据
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0");
//        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, ConstantsClient::getConnectTimeOut());//连接超时，这个数值如果设置太短可能导致数据请求不到就断开了
//        curl_setopt($curl, CURLOPT_TIMEOUT, ConstantsClient::getResponseTimeOut());       //接收数据时超时设置，如果在设定时间内数据未接收完，直接退出
    $responseText = curl_exec($curl);

    if (false === $responseText) {
        writeLog('curl报错：'.curl_error($curl));
        return fasle;
    }
    curl_close($curl);
    return $responseText;
}

/**
 * 远程获取数据，GET模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * @return 远程输出的数据
 */
function doGet($url, $cacert_url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, ConstantsClient::getConnectTimeOut());//连接超时，这个数值如果设置太短可能导致数据请求不到就断开了
    curl_setopt($curl, CURLOPT_TIMEOUT, ConstantsClient::getResponseTimeOut());       //接收数据时超时设置，如果在设定时间内数据未接收完，直接退出
    $responseText = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $responseText;
}

/**
 * 日志记录
 * @param string $log 日志内容
 * @param string $type 日志类型，默认'ERROR'
 */
function writeLog($log, $type = 'ERROR') {
    $log_file = dirname(__FILE__) . '/../log/' . date('Ymd') . '.log';
    $log_content = '[' . strtoupper($type) . ']: ' . date('Y-m-d H:i:s') . ' ' . $log . "\r\n";

    if($fp = fopen($log_file,'a')){
        shell_exec("chown apache:apache ".$log_file);//TODO:没有考虑非apache的情况，也没有考虑文件夹的权限
        shell_exec("chmod 666 ".$log_file);
        //获取文件独占锁
        $canWrite = flock($fp,LOCK_EX);
        if($canWrite)
            fwrite($fp,$log_content);
        //释放锁定,关闭文件
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * 数组（或对象）转xml
 * @param mixed  $data 数组（或对象）
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function array_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? array_to_xml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}

/**
 * xml转数组
 * @param string $xml xml字符串
 * @return mixed 返回数组
 */
function xml_to_array($xml) {
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $arr;
}

/**
 * 截取<body>和</body>之间的字符串
 * @param string $str 需要截取的字符串
 * @param string $begin 开始字符串
 * @param string $end 结束字符串
 * @return string
 */
function subStrXml($str, $begin, $end){
    $b = strpos($str, $begin);
    $c = strrpos($str, $end);
    if ($b === false){
        return '';
    }

    return substr($str, $b, $c-$b+strlen($end));
}

/** 
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param  array $param 需要拼接的数组 
 * @return string       拼接完成以后的字符串 
 */ 
function createLinkstring($param)
{
    $arg = "";
    if (is_array($param)) {
        foreach ($param as $key => $val) {
            if ($val == '') continue;
            $arg .= "{$key}={$val}&";
        }
        //去掉最后一个&字符
        $arg = rtrim($arg, '&');
    } else {
        $arg = $param;
    }

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }

    return $arg;
}
