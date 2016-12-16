<?php
/**
 * 地图控制器
 *
 * @package Controller 
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
class MapController extends Yaf_Controller_Abstract {
    
    //附近的自行车数量
    const COUNT = 30;
    
    
    //显示附近的自行车
    public function nearAction() {
        $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
        $lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
        $bikeid = filter_input(INPUT_GET, 'bikeid');
        $show_all = filter_input(INPUT_GET, 'showall', FILTER_VALIDATE_BOOLEAN);
        
        //获取附近的自行车数据
        $num = $show_all ? false : self::COUNT;
        $bike = BikeModel::showNearBike($lat, $lng, $num);
        
        //获取配置文件
        $env = new Yaf_Config_Ini(APP_PATH . 'conf/application.ini', 'env');
        
        $js_config = array(
            'baidu_ak'  => $env['baidu_ak'],
            'lat'       => $lat,
            'lng'       => $lng,
            'bike'      => $bike,
            'bikeid'    => $bikeid,
        );
        $this->getView()->assign(array(
            'js_config'  => $js_config,
        ));
    }
    
}

 