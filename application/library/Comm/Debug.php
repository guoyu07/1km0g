<?php
/**
 * 调试基础类
 *
 * @package Comm 
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Comm;
class Debug {
    
    /**
     * 写入LOG
     * 
     * @param string $msg    消息内容
     * @param string $file   文件名
     * @param string $domain Storage域
     * 
     * @return void
     */
    static public function log($msg, $file = 'debug.txt', $domain = 'debug') {
        
        return false;
        
        $content = $msg . "\r\n";
        $content .= 'TIME:' . date("Y-m-d H:i:s\r\n");
        $content .= "GET:" . print_r($_GET, true) . "\r\n";
        $content .= "POST:" . print_r($_POST, true) . "\r\n";
        $content .= "SERVER:" . print_r($_SERVER, true) . "\r\n";
        $content .= "RAW_POST:" . print_r($GLOBALS['HTTP_RAW_POST_DATA'], true) . "\r\n";
        $sae_storage = new \SaeStorage();
        $sae_storage->write($domain, $file, $content);
    }
    
}
 
 