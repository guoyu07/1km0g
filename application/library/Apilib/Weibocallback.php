<?php
/**
 * 微博消息接口
 *
 * @package Apilib
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */

namespace Apilib;
include APP_PATH . 'application/library/Thirdpart/Weibo/CallbackSDK.php';
class Weibocallback {

    /**
     * 微博回调SDK
     *
     * @var \CallbackSDK
     */
    protected $_wb_cb = null;

    /**
     * 静态类禁止实例化，仅能通过init实例
     *
     * @return void
     */
    protected function __construct() {
        $conf_weibo_open= (new \Yaf_Config_Ini(APP_PATH . 'conf/weibo.ini'));
        $this->_wb_cb = new \CallbackSDK();
        $this->_wb_cb->setAppSecret($conf_weibo_open->open->app_secret);


        //签名验证
        $request = \Yaf_Dispatcher::getInstance()->getRequest();
        $signature = $request->getQuery('signature');
        $timestamp = $request->getQuery('timestamp');
        $nonce = $request->getQuery('nonce');
        if (!$this->_wb_cb->checkSignature($signature, $timestamp, $nonce)) {
            throw new \Exception\Weibocallback('check signature error');
        }
    }

    /**
     * 静态类禁止克隆
     *
     * @return boolean
     */
    public function __clone() {
        return false;
    }

    /**
     * 获取要操作的对象
     * @return \CallbackSDK
     */
    static public function init() {
        static $object = null;
        if($object === null) {
            $object = new self();
        }
        return $object;
    }

    /**
     * 获取POST提交过来的参数
     *
     * @return array()
     */
    public function getPostMsgStr() {
        //{"text":"我在这里: http://t.cn/RzPbMRS","type":"position","receiver_id":5342534570,"sender_id":1150169277,"created_at":"Sun Nov 16 21:36:53 +0800 2014","data":{"longitude":"116.331830","latitude":"39.972070"}}
        $result = null;
        if($result === null) {
            $result = $this->_wb_cb->getPostMsgStr();
            $result = $result ? $result : array();
        }
        return $result;
    }

    /**
     * 回复消息
     *
     * @param int    $receiver_id 发送者
     * @param int    $sender_id   接收者
     * @param array  $data        数据内容
     * @param string $data_type   数据类型
     *
     * @return string
     */
    public function reply($receiver_id, $sender_id, array $data, $data_type) {
        $str = $this->_wb_cb->buildReplyMsg($receiver_id, $sender_id, $data, $data_type);
        return json_encode($str);
    }

    /**
     * 回复文本消息
     *
     * @param string  $text   文本消息内容
     * @param boolean $output 是否直接输出（默认否）
     *
     * @return strig 要返回的内容
     */
    public function replyText($text, $output = false) {
        $post_msg = $this->getPostMsgStr();

        $result = $this->reply(
            $post_msg['sender_id'],   // sender_id为发送回复消息的uid，即蓝v自己
            $post_msg['receiver_id'],     // receiver_id为接收回复消息的uid，即蓝v的粉丝
            $this->_wb_cb->textData($text),
            'text'
        );
        if($output) {
            echo $result;
        }
        //\Comm\Debug::log($result);

        return $result;
    }

    /**
     * 回复富内容消息
     *
     * @param array  $articles 富文本内容
     * @param boolean $output  是否直接输出（默认否）
     *
     * @return string
     */
    public function replyArticle(array $articles, $output = false) {
        $post_msg = $this->getPostMsgStr();

        $result = $this->reply(
                $post_msg['sender_id'],   // sender_id为发送回复消息的uid，即蓝v自己
                $post_msg['receiver_id'],     // receiver_id为接收回复消息的uid，即蓝v的粉丝
                $this->_wb_cb->articleData($articles),
                'articles'
        );
        if($output) {
            echo $result;
        }
        \Comm\Debug::log($result, 'articles.txt');

        return $result;
    }
}
