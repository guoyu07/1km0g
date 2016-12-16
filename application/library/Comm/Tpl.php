<?php
/**
 * 模板基础类
 *
 * @package Comm
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Comm;
abstract class Tpl {
    
    /**
     * 获取当前静态文件版本号（SVN每次提交均更新）
     * 
     * @return int
     */
    static public function version() {
        static $result = null;
        if($result === null) {
            $env = new \Yaf_Config_Ini(APP_PATH . 'conf/application.ini', 'env');
            $result = $env['view_version'];
        }
        return $result;
    }
    
    /**
     * 加载JS
     * 
     * @param string  $url          JS路径
     * @param boolean $show_version 是否显示版本
     * 
     * @return string
     */
    static public function js($url, $show_version = true) {
        if($show_version) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= 'ver=' . self::version();
        }
        printf('<script type="text/javascript" src="%s"></script>', $url);
    }
    
    /**
     * 加载CSS
     *
     * @param string  $url          CSS路径
     * @param boolean $show_version 是否显示版本
     *
     * @return string
     */
    static public function css($url, $show_version = true) {
        if($show_version) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= 'ver=' . self::version();
        }
        
        printf('<link href="%s" type="text/css" rel="stylesheet" />', $url);
    }
}
 
 