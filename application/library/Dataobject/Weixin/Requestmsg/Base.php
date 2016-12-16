<?php

/**
 * 微信请求消息体
 *
 * @package Dataobject
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Dataobject\Weixin\Requestmsg;
class Base {
    public $FromUserName = '';
    public $ToUserName = '';
    public $CreateTime = '';
    public $MsgId = '';
    public $MsgType = '';

    /**
     * 设置数据
     *
     * @param mixed $data array/object
     */
    public function setData($data) {
        foreach($data as $key => $value){
            $this->$key = (string)$value;
        }
    }
}
