<?php
/**
 * 自行车数据
 *
 * @package Model
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
class BikeModel {

    /**
     * 获取自行车数据(在KV中缓存10秒钟)
     *
     * @todo Cache
     *
     * @return array
     */
    static public function showBikeData() {
        $cache_time = 10;
        $data = Comm\Kv::get('bikedata');
        if(!is_array($data) || (NOW > $data['expire'] && Comm\Mc::getLock('bikedata', $cache_time)) ) {
            //没有KV数据或者（持久化数据过期并单进程锁定）
            $result = self::_showBikeOriData();
            $data = array(
                'expire' => NOW + $cache_time,
                'result' => $result,
            );
            Comm\Kv::set('bikedata', array(), $data);
            Comm\Mc::unLock('bikedata');
        } else {
            $result = $data['result'];
        }

        return $result;
    }

    /**
     * 获取原始自行车数据
     *
     * @return array
     */
    static public function _showBikeOriData() {
        $url = 'http://www.1km0g.com/api/ibikeJSInterface.asp';
        $sae_fetch_url = new SaeFetchurl();
        $data = $sae_fetch_url->fetch($url);

        $result = array();
        if(preg_match('/{.*}/s', $data, $regex_data)) {
            $result = json_decode($regex_data[0], true);
        }

        return $result;
    }

    /**
     * 获取附近的自行车数据
     * @param float $lat 经
     * @param float $lng 纬
     * @param mixed $num 要获取的数量
     *
     * @return array
     */
    static public function showNearBike($lat, $lng, $num = null) {
        $bike_data = self::showBikeData();
        $station = $bike_data['station'];
        $bike_sort = array();
        foreach($station as $key => $value) {
            $lat_lng_size = sqrt(pow(abs($lat - $value['lat']), 2) + pow(abs($lng - $value['lng']), 2));
            $station[$key]['lat_lng_size'] = $lat_lng_size;
            $bike_sort[$key] = $lat_lng_size;
        }

        array_multisort($bike_sort, SORT_ASC, SORT_NUMERIC, $station);
        if(is_numeric($num)) {
            $station = array_slice($station, 0, $num);
        }
        return $station;
    }

    /**
     * 根据ID获取自行车数据
     *
     * @param int $bike_id
     *
     * @return array
     */
    static public function showById($bike_id) {
        $bike_data = self::showBikeData();
        $result = array();
        foreach($bike_data['station'] as $value) {
            if($value['id'] == $bike_id) {
                $result = $value;
                break;
            }
        }
        return $result;
    }

    /**
     * 根据名字获取列表
     * @param type $name
     * @param type $total
     * @return array
     */
    static public function showListByName($name, $total) {
        $bike_data = self::showBikeData();
        $result = $result_address = array();
        foreach($bike_data['station'] as $value) {
            if(strpos($value['name'], $name) !== false) {
                $result[] = $value;
                if(count($result) >= $total) {
                    break;
                }
            } elseif(strpos($value['address'], $name) !== false) {
                $result_address[] = $value;
            }
        }

        if(count($result) < $total) {
            $result = array_merge($result, array_slice($result_address, 0, $total - count($result)));
        }
        return $result;
    }

    /**
     * 获取自行车图片
     *
     * @param float $lat    经
     * @param float $lng    纬
     * @param int   $width  宽
     * @param int   $height 高
     *
     * @return array
     */
    static public function showMapThumb($lat, $lng, $width, $height) {
        $last_version = 4;
        $kv_param = array($lat, $lng, $width, $height);
        $result = Comm\Kv::get('mapthumb', $kv_param);

        //版本过低，删除持久化存储的数据
        if($result['ver'] < $last_version) {
            $storage = new SaeStorage();
            $storage->delete('bike', $result['img']);
        }


        //初次数据或版本过低，重新生成图片
        if(!$result || $result['ver'] < $last_version){
            $img_path = self::_fetchMapThumbUrl($lat, $lng, $width, $height);
            if($img_path) {
                $result = array(
                    'ver' => $last_version,
                    'img' => $img_path,
                );
                Comm\Kv::set('mapthumb', $kv_param, $result);
            } else {
                $result = array();
            }
        }

        return $result;
    }

    /**
     * 生成图片并写入持久化存储
     *
     * @param float $lat    经
     * @param float $lng    纬
     * @param int   $width  宽
     * @param int   $height 高
     *
     * @return string 生成的图片
     */
    static public function _fetchMapThumbUrl($lat, $lng, $width, $height) {

        //拼接图片URL
        $image = 'http://api.map.baidu.com/staticimage?' . http_build_query(array(
            'zoom'      => '12',
            'center'    => "$lat,$lng",
            'width'     => $width,
            'height'    => $height,
            'markers'   => "$lat, $lng",
            'copyright' => '1',

        ));

        //获取图片数据
        $f = new SaeFetchurl();
        $content = $f->fetch($image);

        //写入持久化存储
        $path = "map_thumb/{$width}x{$height}/{$lat}_{$lng}.png";
        $storage = new SaeStorage();
        $url = $storage->write('bike', $path, $content);
        if($url) {
            $result = $path;
        } else {
            $result = false;
        }
        return $result;
    }

}

