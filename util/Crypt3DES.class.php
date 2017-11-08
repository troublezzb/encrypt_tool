<?php
/**
 * 3DES加密、解密类
 * 3DES（即Triple DES）是DES向AES过渡的加密算法。
 * 注意：
 * 1、JAVA和.NET填充模式使用的是PKCS7。所以PHP需要手动实现PKCS7模式补位填充。
 */
class Crypt3DES
{
    private $_key = null;//加密key（长度为24字节）
    private $_iv = null;//加密向量（长度为8个字节，如：12345678）
    private $_useBase64 = null;//是否使用base64二次加密

    /**
     * 构造
     * @param string $crypt_key 加密key
     * @param string $crypt_iv 加密向量
     */
    function __construct($crypt_key, $crypt_iv, $useBase64=true)
    {
        $this->_key = $crypt_key;
        $this->_iv = $crypt_iv;
        // 是否使用base64
        $this->_useBase64 = $useBase64;
    }

    /**
     * 加密
     * @param string $value 待加密的字符串
     * @return string $ret 密文
     */
    public function encrypt($value)
    {
        //1、打开3DES算法的cbc模式
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        //检查加密key，iv的长度是否符合算法要求  
        $key = $this->fixLen($this->_key, mcrypt_enc_get_key_size($td));  
        $iv = $this->fixLen($this->_iv, mcrypt_enc_get_iv_size($td));
        //2、初始化加密所需的缓冲区
        mcrypt_generic_init($td, $key, $iv);
        //使用PKCS7填充
        $value = $this->PaddingPKCS7($value);
        //3、开始加密
        $ret = mcrypt_generic($td, $value);
        //若使用base64二次加密
        if($this->_useBase64){
            $ret = base64_encode($ret);
        }
        //4、释放资源
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    /**
     * 解密
     * @param string $value 密文
     * @return string $ret 明文
     */
    public function decrypt($value)
    {
        //1、打开3DES算法的cbc模式
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        //检查加密key，iv的长度是否符合算法要求  
        $key = $this->fixLen($this->_key, mcrypt_enc_get_key_size($td));  
        $iv = $this->fixLen($this->_iv, mcrypt_enc_get_iv_size($td)); 
        //2、初始化加密所需的缓冲区
        mcrypt_generic_init($td, $key, $iv);
        //使用base64二次加密
        if($this->_useBase64){
            $value = base64_decode($value);
        }
        //3、开始解密
        $ret = trim(mdecrypt_generic($td, $value));
        //去除PKCS7填充
        $ret = $this->UnPaddingPKCS7($ret);
        //4、释放资源
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    /**
     * PKCS7填充
     * @param string $data 字符串
     * @return string $data 填充后的字符串
     */
    private function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size('tripledes', 'cbc');
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    /**
     * 去除PKCS7填充
     * @param string $data 字符串
     * @return string $data 去除填充后的字符串
     */
    private function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
    * 返回适合算法长度的key，iv字符串
    * @param string $str key或iv的值
    * @param int $td_len 符合条件的key或iv长度
    * @return string 返回处理后的key或iv值
    */
    private function fixLen($str, $td_len)
    {
        $str_len = strlen($str);
        if ($str_len > $td_len) {
            return substr($str, 0, $td_len);
        } else if($str_len < $td_len) {
            return str_pad($str, $td_len, '0');//长度不足补零
        }
        return $str;
    }
}
?>
