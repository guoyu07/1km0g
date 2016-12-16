<?php
/**
 * 基础消息
 *
 * @package Dataobject
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Dataobject\Weixin\Responsemsg;
abstract class Base {

    public $ToUserName = '';
    public $FromUserName = '';
    public $CreateTime = '';
    public $MsgType = '';

    /**
     * 构造方法
     *
     * @param \Dataobject\Weixin\Requestmsg\Base $request_msg
     */
    public function __construct(\Dataobject\Weixin\Requestmsg\Base $request_msg) {
        $this->ToUserName = $request_msg->FromUserName;
        $this->FromUserName = $request_msg->ToUserName;
        $class_name = get_class($this);
        $class_name = substr($class_name, strrpos($class_name, '\\') + 1);
        $this->MsgType = strtolower($class_name);
        $this->CreateTime = time();
    }

    /**
     * 获取响应消息
     *
     * @return string
     */
    public function show() {
        $result = '<xml>';
        foreach($this as $key => $value) {
            if(substr($key, 0, 1) !== '_') {
                $result .= "<{$key}><![CDATA[{$value}]]></{$key}>";
            }
        }
        $this->_beforeEnd($result);
        $result .= '</xml>';
        return $result;
    }

    /**
     * 结束前
     * @param string $result
     */
    protected function _beforeEnd(& $result) {

    }

}