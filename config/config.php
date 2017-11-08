<?php
/**
 * 支付接入配置类
 */
class Config
{

    private $config = array(
        'default_encrypt_type' => 'AES',//默认的加密类型：RSA/AES/3DES
    );

    /**
     * 获取key
     * @param $key
     * @return null
     */
    public function key($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
}

