<?php
/**
 * 图文响应消息
 *
 * @package  Dataobject
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Dataobject\Weixin\Responsemsg;
class News extends Base {

    public $ArticleCount = 0;
    protected $_articles = '';

    /**
     * 添加一条图文消息
     *
     * @param type $title
     * @param type $description
     * @param type $picurl
     * @param type $url
     */
    public function addArticles($title, $description, $picurl, $url) {
        $this->_articles[] = array(
            'title' => $title,
            'description' => $description,
            'picurl'    => $picurl,
            'url'   => $url,
        );
        ++$this->ArticleCount;
        return $this;
    }

    /**
     * 生成XML结束前插件
     *
     * @param string $result
     */
    protected function _beforeEnd(& $result) {
        $result .= '<Articles>';
        $xml_articles = '<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
        </item>';
        foreach($this->_articles as $value) {
            $result .= sprintf($xml_articles, $value['title'], $value['description'], $value['picurl'], $value['url']);
        }
        $result .= '</Articles>';
    }

}

