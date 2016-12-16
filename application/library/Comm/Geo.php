<?php

/**
 * 地图坐标基础类
 *
 * @package Comm
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */

namespace Comm;

abstract class Geo {

    /**
     * GCJ-02转换BD-09
     *
     * @param float $lat
     * @param float $lng
     *
     * @return float
     */
    public static function gcj2baidu($lat, $lng) {
        $v = M_PI * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;

        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $v);
        $t = atan2($y, $x) + 0.000003 * cos($x * $v);

        return array(
            'lat' => $z * sin($t) + 0.006,
            'lng' => $z * cos($t) + 0.0065
        );
    }

}
