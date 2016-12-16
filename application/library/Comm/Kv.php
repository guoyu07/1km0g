<?php
/**
 * Kv操作
 *
 * @package Comm
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Comm;
abstract class Kv {
    
    /**
     * 获取KV操作对象
     * 
     * @return SaeKV
     */
    static public function kvobj() {
        static $kv = null;
        if($kv === null) {
            $kv = new \SaeKV();
            $kv->init();
        }
        return $kv;
    }
    
    /**
     * 获取KV中的数据
     * 
     * @param string $name  KV配置中的名字
     * @param array  $param 参数
     * 
     * @return mixed 
     */
    static public function get($name, array $param = array()) {
        $config = self::showConfig($name, $param);
        $result = self::kvobj()->get($config['key_full']);
        return self::_processResult($result, $config);
    }
    
    /**
     * 设置KV中的数据
     *
     * @param string $name  KV配置中的名字
     * @param array  $param 参数
     * @param mixed  $value 要写入的值
     *
     * @return mixed
     */
    static public function set($name, array $param = array(), $value) {
        $config = self::showConfig($name, $param);
        $value = self::_prepareData($value, $config);
        self::kvobj()->set($config['key_full'], $value);
    }
    
    /**
     * 获取配置
     * 
     * @param string $name  名字   
     * @param array  $param 参数
     * 
     * @return array
     */
    static public function showConfig($name, array $param) {
        $conf = new \Yaf_Config_Ini(APP_PATH . 'conf/kv.ini', $name);
        $conf = $conf->toArray();
        $conf['key_full'] = vsprintf($conf['key'], $param);
        return $conf;
    }
    
    /**
     * 预备要处理数据
     * 
     * @param mixed $value  原数据
     * @param array $config 处理后的结果
     * 
     * @return string
     */
    static protected function _prepareData($value, array $config) {
        if($value !== false) {
            switch($config['type']) {
                case 'json' :
                    //JSON处理
                    $value = json_encode($value);
                    break;
                case 'serialize' :
                    //序列化处理
                    $value = serialize($value);
                    break;
            }
        }
        return $value;
    }
    
    /**
     * 处理从KV读取出来的结果
     * 
     * @param mixed $result 原结果
     * @param array $config 处理后的结果
     * 
     * @return mixed
     */
    static protected function _processResult($result, array $config) {
        switch($config['type']) {
            case 'json' :
                //JSON处理
                $result = json_decode($result, true);
                break;
            case 'serialize' :
                //序列化处理
                $result = unserialize($result);
                break;
        }
        return $result;
    }
    
    
    
}
 