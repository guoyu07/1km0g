<?php
/**
 * 百度地图API
 *
 * @package Apilib
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
namespace Apilib;
class Baidumap {


    /**
     * 获取指定位置修正后的坐标
     *
     * @param string $location x,y
     *
     * @return array
     */
    static public function coords($location) {
        $env = new \Yaf_Config_Ini(APP_PATH . 'conf/application.ini', 'env');
        $url = 'http://api.map.baidu.com/geoconv/v1/?coords=%s&from=3&to=5&ak=' . $env['baidu_ak_server'];

        $url = sprintf($url, $location);
        $sae_fetch_url = new \SaeFetchurl();
        $result = $sae_fetch_url->fetch($url);
        $result = json_decode($result, true);

        $result = isset($result['result'][0]) ? $result['result'][0] : array();
        return $result;
    }

}

