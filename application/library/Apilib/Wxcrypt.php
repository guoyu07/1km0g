<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Apilib;

include APP_PATH . 'application/library/Thirdpart/Wxcrypt/wxBizMsgCrypt.php';

class Wxcrypt {

    /**
     * 微信对象
     * @var \WXBizMsgCrypt 
     */
    protected $_wx;

    /**
     * 构造方法
     * 
     * @param \Yaf_Config_Simple $config
     */
    public function __construct(\Yaf_Config_Abstract $config) {
        $this->_wx = new \WXBizMsgCrypt($config->token, $config->encoding_aes, $config->appid);
    }

    /**
     * 解密消息
     * 
     * @return string
     */
    public function decodeRequest() {
        $post_str = $GLOBALS['HTTP_RAW_POST_DATA'];
        $nonce = filter_input(INPUT_GET, 'nonce');
        $timestamp = filter_input(INPUT_GET, 'timestamp');
        $msg_sign = filter_input(INPUT_GET, 'msg_signature');
        $result = '';
        $err_code = $this->_wx->decryptMsg($msg_sign, $timestamp, $nonce, $post_str, $result);
        return $result;
    }

    /**
     * 加密数据
     * @param string $data
     * @return string
     */
    public function encode($data) {
        $result = '';
        $nonce = filter_input(INPUT_GET, 'nonce');
        $timestamp = filter_input(INPUT_GET, 'timestamp');
        $err_code = $this->_wx->encryptMsg($data, $timestamp, $nonce, $result);
        return $result ? $result : $data;
    }

}
