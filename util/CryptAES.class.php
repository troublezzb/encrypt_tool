<?php
/**
 * AES加密、解密类
 * 高级加密标准（Advanced Encryption Standard，AES），又称Rijndael加密法，于2001年11月26日发布，用来替代原先的DES。
 * 用法：
 * <pre>
 * // 实例化类
 * $cryptAES = new CryptAES();
 * $string = 'laohu';
 * // 加密
 * $encodeString = $cryptAES->encrypt($string);
 * // 解密
 * $decodeString = $cryptAES->decrypt($encodeString);
 * </pre>
 * 注意：
 * 1、跨语言加解密的要求是：AES/CBC/ZeroPadding 128位模式，key和iv一样，编码统一用utf-8。不支持ZeroPadding的就用NoPadding。
 */
class CryptAES
{
    private $_bit = null;//加密位：支持128、192、256位
    private $_type = null;//加密模式：支持cbc（加密分组链接模式）、cfb、ctr、ecb、ncfb、nofb、ofb、stream等
    private $_key = null;//加密key：必须16字节或24字节
    private $_ivSize = null;
    private $_iv = null;//加密向量
    private $_useBase64 = null;//是否使用base64二次加密

    /**
     * 默认使用AES/CBC/ZeroPadding 128位模式，且使用base64二次加密
     * @param string $key 加密key：必须16字节或24字节
     * @param string $iv 加密向量：16字节
     * @param int $bit 加密位
     * @param string $type 加密模式
     * @param boolean $useBase64 是否使用base64二次加密
     */
    public function __construct($key, $iv, $bit = 128, $type = 'cbc', $useBase64 = true){
        // 检测环境支持
        $this->checkEnv($type);
        // 加密位
        $bitArr = array(
            '128' => MCRYPT_RIJNDAEL_128,
            '192' => MCRYPT_RIJNDAEL_192,
            '256' => MCRYPT_RIJNDAEL_256,
        );
        $this->_bit = $bitArr[$bit];
        // 加密模式
        $typeArr = array(
            'cbc' => MCRYPT_MODE_CBC,
            'cfb' => MCRYPT_MODE_CFB,
            'ecb' => MCRYPT_MODE_ECB,
            'nofb' => MCRYPT_MODE_NOFB,
            'ofb' => MCRYPT_MODE_OFB,
            'stream' => MCRYPT_MODE_STREAM,
        );
        $this->_type = $typeArr[$type];
        // 加密key
        $this->_key = $key;
        //加密向量
        # 为 CBC 模式创建随机的初始向量
        //$this->_ivSize = mcrypt_get_ivSize($this->_bit, $this->_type);
        //$this->_iv = mcrypt_create_iv($this->_ivSize, MCRYPT_RAND);
        $this->_iv = $iv;

        // 是否使用base64
        $this->_useBase64 = $useBase64;
    }

    /**
     * 检测环境是否支持实例化
     */
    public function checkEnv($type){
        $mode_list = mcrypt_list_modes(); //mcrypt支持的加密模式列表
        if (!in_array($type, $mode_list)) {
            die('服务器环境不支持该加密模式：'.$type);
        }
    }

    /**
     * 加密
     * @param string $string 待加密字符串
     * @return string
     */
    public function encrypt($string){
       //
        if(MCRYPT_MODE_ECB === $this->_type){
            $encodeString = mcrypt_encrypt($this->_bit, $this->_key, $string, $this->_type);
        }else{
            $encodeString = mcrypt_encrypt($this->_bit, $this->_key, $string, $this->_type, $this->_iv);
        }
        if($this->_useBase64){
            $encodeString = base64_encode($encodeString);
        }
        return $encodeString;
    }

    /**
     * 解密
     * @param string $string 待解密字符串
     * @return string
     */
    public function decrypt($string){
        if($this->_useBase64){
            $string = base64_decode($string);
        }
        if(MCRYPT_MODE_ECB === $this->_type){
            $decodeString = mcrypt_decrypt($this->_bit, $this->_key, $string, $this->_type);
        }else{
            $decodeString = mcrypt_decrypt($this->_bit, $this->_key, $string, $this->_type, $this->_iv);
        }
        //PHP只有NoPadding填充，但实际上会以“\0”来填充（ZeroPadding）。解码后别忘了trim掉就行了。
        $decodeString = rtrim($decodeString, "\0");
        return $decodeString;
    }


}