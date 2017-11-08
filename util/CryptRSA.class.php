<?php
/**
 * RSA非对称加密算法
 * 第一个能同时用于加密和数字签名的算法。
 * 1977年由Ron Rivest、Adi Shamir和Leonard Adleman三人提出，已被ISO推荐为公钥数据加密标准，广泛使用。
 * RSA的安全基于大数分解的难度。其公钥和私钥是一对大素数（100到200位十进制数或更大）的函数。
 * 该算法私钥越长，越难破解。2009年12月，768位被成功分解。所以，1024位的基本安全，2048位的极其安全。
 * 缺点：
 *     1、加密的计算量很大。所以一般会组合对称加密（如3DES、AES）。
 */
class CryptRSA
{
    private $_pubKey = null;//公钥
    private $_priKey = null;//私钥
    private $_useBase64 = null;//是否使用base64加密

    /**
     * 构造函数
     * @param string 公钥（加密和验签时传入）
     * @param string 私钥（解密和签名时传入）
     */
    public function __construct($pubKey='', $priKey='', $useBase64=true)
    {
        if ($pubKey) {
            $this->_pubKey = openssl_get_publickey($pubKey);
        }
        if ($priKey) {
            $this->_priKey = openssl_get_privatekey($priKey);
        }
        $this->_useBase64 = $useBase64;
    }

    /**
     * 公钥加密
     * @param string 明文
     * @param int 填充方式（貌似php有bug，暂时仅支持OPENSSL_PKCS1_PADDING）（为了安全，每次加密的密文会不一样）
     * @return string 密文
     */
    public function encrypt($string, $padding = OPENSSL_PKCS1_PADDING){
        $ret = false;
        if (openssl_public_encrypt($string, $result, $this->_pubKey, $padding)){
            if($this->_useBase64){
                $ret = base64_encode($result);
            }
        }
        return $ret;
    }

    /**
     * 私钥解密
     * @param string 密文
     * @param int 填充方式（暂时仅支持OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt($string, $padding = OPENSSL_PKCS1_PADDING, $rev = false){
        if ($string === false) {
            return false;
        }
        $ret = false;
        if($this->_useBase64){
            $string = base64_decode($string);
        }
        if (openssl_private_decrypt($string, $result, $this->_priKey, $padding)){
            $ret = $rev ? rtrim(strrev($result), "\0") : ''.$result;
        }
        return $ret;
    }

    /**
     * 生成签名
     * @param string 签名材料
     * @return 签名值
     */
    public function sign($string, $signature_alg = OPENSSL_ALGO_SHA1){
        $ret = false;
        if (openssl_sign($string, $result, $this->_priKey, $signature_alg)){
            if($this->_useBase64){
                $ret = base64_encode($result);
            }
        }
        // 释放资源
        openssl_free_key($this->_priKey);
        return $ret;
    }

    /**
     * 验证签名
     * @param string 签名材料
     * @param string 签名值
     * @return bool
     */
    public function verify($string, $sign, $signature_alg = OPENSSL_ALGO_SHA1){
        if ($sign === false) {
            return false;
        }
        $ret = false;
        if($this->_useBase64){
            $sign = base64_decode($sign);
        }
        switch (openssl_verify($string, $sign, $this->_pubKey, $signature_alg)){
            case 1://成功
                $ret = true;
                break;
            case 0://失败
                break;
            case -1://报错
                writeLog('公钥详情：'.json_encode(openssl_pkey_get_details($res)));
                writeLog('签名报错：'.openssl_error_string());
                break;
        }
        // 释放资源
        openssl_free_key($this->_pubKey);
        return $ret;
    }

}
?>
