<?php

/**
 * 微信回调接口接口
 *
 * @package Apilib
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */

namespace Apilib;

class Weixincallback {

    /**
     * 构造方法，验证来源合法性
     *
     * @return void
     */
    public function __construct() {
        $this->_checkSignature();
    }

    /**
     * 获取请求消息
     *
     * @param string $post_str 提交的字符串（如果是加密的，解密后传入）
     * @return \Dataobject\Weixin\Requestmsg\Base
     */
    public function getRequestMsg($post_str = null) {
        !$post_str && $post_str = file_get_contents('php://input');
        $class = '\\Dataobject\\Weixin\\Requestmsg\\Base';
        if($post_str) {
            $post_obj = simplexml_load_string($post_str, 'SimpleXMLElement', LIBXML_NOCDATA);
            $class = '\\Dataobject\\Weixin\\Requestmsg\\' . ucfirst($post_obj->MsgType);
            $object = new $class();
            $object->setData($post_obj);
        }
        return $object ? $object : new $class();
    }

    /**
     * 返回一条响应消息
     *
     * @param \Dataobject\Weixin\Responsemsg\Base $response
     * @return string
     */
    public function responseMsg(\Dataobject\Weixin\Responsemsg\Base $response) {
        $result = $response->show();
        return $result;
    }

    /**
     * 检查签名是否正确
     *
     * @return boolean
     */
    protected function _checkSignature() {
        $config_weixin = new \Yaf_Config_Ini(APP_PATH . 'conf/weixin.ini');
        $token = $config_weixin->api->token;


        $signature = filter_input(INPUT_GET, 'signature');
        $timestamp = filter_input(INPUT_GET, 'timestamp');
        $nonce = filter_input(INPUT_GET, 'nonce');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

}
