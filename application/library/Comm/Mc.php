<?php
/**
 * 缓存基础类
 *
 * @package Comm
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Comm;

class Mc_Empty {
    
    public function __call($func, $method) {
        return false;
    }
    
}

class Mc {
    
    /**
     * 初始化MC，获取MC操作对象
     * 
     * @return \Memcache
     */
    static public function init() {
        return new Mc_Empty();
    }
    
    /**
     * 通过ADD方式获取一个锁
     * 
     * @param string $lock_name 锁名称
     * @param number $lock_time 锁时间
     * 
     * @return boolean
     */
    static public function getLock($lock_name, $lock_time = 60) {
        $lock_name = 'lk_' . $lock_name;
        $mc = self::init();
        return $mc->add($lock_name, 1, false, $lock_time);
    }
    
    /**
     * 解锁
     * 
     * @param string $lock_name 锁名称
     * 
     * @return boolean
     */
    static public function unLock($lock_name) {
        $lock_name = 'lk_' . $lock_name;
        $mc = self::init();
        return $mc->delete($lock_name);
    }
    
}
 