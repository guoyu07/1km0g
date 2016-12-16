<?php
/**
 * 调试控制器
 *
 * @package Controller
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
class DevelopController extends Yaf_Controller_Abstract {
    
    //自行车数据
    public function bikeAction() {
        $result = BikeModel::showBikeData();
        print_r($result);
        return false;
        
    }
    
    //附近的自行车测试
    public function nearbikeAction() {
        $result = BikeModel::showNearBike(116.331990, 39.972100, 20);
        print_r($result);
        return false;
    }
    
    //获取MC
    public function mcAction() {
        $mc = Comm\Mc::init();
        
        var_dump(Comm\Mc::getLock('debug11', 3));
        var_dump(memcache_get($mc, 'lk_debug11'));
        
        return false;
    }
    
    //显示PHPINFO
    public function phpinfoAction() {
        phpinfo();
        return false;
    }
}
 