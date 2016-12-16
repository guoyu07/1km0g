<?php

/**
 * 文本响应消息
 *
 * @package Dataobject
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Dataobject\Weixin\Responsemsg;
class Text extends Base {

    public $Content = '';

    /**
     * 设置文件消息
     *
     * @param string $content
     */
    public function setContent($content) {
        $this->Content = $content;
    }

}

