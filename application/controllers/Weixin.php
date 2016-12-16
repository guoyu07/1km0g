<?php
/**
 * 微信接口
 *
 * @package Controller
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
class WeixinController extends Yaf_Controller_Abstract {
    /**
     * 初始化控制器方法
     *
     * @see Yaf_Controller_Abstract::init()
     */
    public function init() {

        //设置应用类别
        Yaf_Registry::set('application_type', 'weixin');

        //禁止自动渲染模板
        $dispatcher = Yaf_Dispatcher::getInstance();
        $dispatcher->autoRender(false);
        $dispatcher->disableView();
    }

    /**
     * 首页
     *
     * @return void
     */
    public function indexAction() {
        $echostr = filter_input(INPUT_GET, 'echostr');
        $weixin = new \Apilib\Weixincallback();
        if($echostr) {
            echo $echostr;
        } else {
            $request = $weixin->getRequestMsg();
            if($request instanceof \Dataobject\Weixin\Requestmsg\Location) {
                //处理地理位置
                $response = $this->_location($request);
            } elseif ($request instanceof \Dataobject\Weixin\Requestmsg\Text) {
                //处理文本
                $response = $this->_text($request);
            } elseif ($request instanceof \Dataobject\Weixin\Requestmsg\Image) {
                //调试图片
                \Comm\Debug::log(print_r($request, true));
            }

            if($response instanceof \Dataobject\Weixin\Responsemsg\Base) {
                echo $weixin->responseMsg($response);
            }
        }
    }

    /**
     * 处理地理位置数据
     * @param \Dataobject\Weixin\Requestmsg\Location $request
     */
    protected function _location(\Dataobject\Weixin\Requestmsg\Location $request) {

        //修正坐标
//        $coords = Apilib\Baidumap::coords("{$request->Location_Y},{$request->Location_X}");
        $coords = Comm\Geo::gcj2baidu($request->Location_Y, $request->Location_X);
        if($coords) {
            $location_x = $coords['lat'];
            $location_y = $coords['lng'];
        } else {
            $location_x = $request->Location_Y;
            $location_y = $request->Location_X;
        }
        $response = new \Dataobject\Weixin\Responsemsg\News($request);

        $station = BikeModel::showNearBike($location_x, $location_y, 5);

        $i = 0;
        foreach($station as $value) {
            $this->_addBikeArticles($response, $value, $i, $location_x, $location_y);
            ++$i;
        }

        //追加一个查看全部
        $response->addArticles(
            '查看全部车位',
            '点击查看全部车位',
            '',
            $this->_showMapUrl($location_x, $location_y) . '&showall=1'
        );
        return $response;
    }

    /**
     * 显示地图
     *
     * @param float $location_x
     * @param float $location_y
     *
     * @return string
     */
    protected function _showMapUrl($location_x, $location_y){
        return 'http://' . filter_input(INPUT_SERVER, 'HTTP_APPNAME') . ".sinaapp.com/index.php/map/near?lat={$location_x}&lng={$location_y}&from=weixin";
    }

    /**
     * 往响应信息中追加一个Articles
     *
     * @param \Dataobject\Weixin\Responsemsg\News $response
     * @param array $value
     * @param int $i
     * @param float $location_x
     * @param float $location_y
     *
     * @return void
     */
    protected function _addBikeArticles(\Dataobject\Weixin\Responsemsg\News $response, array $value, $i = 0, $location_x = null, $location_y = null) {
        if(isset($value['lat_lng_size'])) {
            $display_name = sprintf(
                "%u.%s：%.02fkm\n【剩】%u【空】%u",
                $value['id'],
                $value['name'],
                $value['lat_lng_size'] * 111,
                $value['availBike'],
                $value['capacity'] - $value['availBike']
            );
        } else {
            $display_name = sprintf(
                "%u.%s：【剩】%u【空】%u",
                $value['id'],
                $value['name'],
                $value['availBike'],
                $value['capacity'] - $value['availBike']
            );
        }


        if($i === 0) {
            $width = 640;
            $height = 320;
        } else {
            $width = $height = 80;
        }
        $image = 'http://api.map.baidu.com/staticimage?' . http_build_query(array(
            'zoom'      => '14',
            'center'    => "{$value['lat']},{$value['lng']}",
            'width'     => $width,
            'height'    => $height,
            'markers'   => "{$value['lat']},{$value['lng']}",
            'copyright' => '1',
        ));

        if(!$location_x || !$location_y) {
            $location_x = $value['lat'];
            $location_y = $value['lng'];
        }
        $map_url = $this->_showMapUrl($location_x, $location_y);
        $response->addArticles($display_name, $value['address'], $image, $map_url . '&bikeid=' . $value['id']);
    }

    /**
     * 处理文本数据
     *
     * @param \Dataobject\Weixin\Requestmsg\Text $request
     */
    protected function _text(\Dataobject\Weixin\Requestmsg\Text $request) {
        $content = trim($request->Content);


        //根据站号获取信息
        if(is_numeric($content)) {
            $bikedata = BikeModel::showById($content);
            if($bikedata) {
                $response = new Dataobject\Weixin\Responsemsg\News($request);
                $this->_addBikeArticles($response, $bikedata);
            } else {
                $response = $this->_defaultText($request);
            }
            return $response;
        }

        //尝试根据名称获取信息
        $bikes = BikeModel::showListByName($content, 8);
        if($bikes) {
            $response = new Dataobject\Weixin\Responsemsg\News($request);
            $i = 0;
            foreach($bikes as $value) {
                $this->_addBikeArticles($response, $value, $i);
                ++$i;
            }
            return $response;
        }

        //默认情况，返回默认文案
        return $this->_defaultText($request);
    }

    protected function _defaultText(\Dataobject\Weixin\Requestmsg\Base $request) {
        $response = new Dataobject\Weixin\Responsemsg\Text($request);
        $response_content = "查看附近的自行车方法：\n";
        $response_content .= "——————————————\n\n";
        $response_content .= "1.附近的车：点击聊天窗口右下角的+号，点“位置”，选择好你的位置后，发送位置即可。\n\n";
        $response_content .= "2.根据车站ID查询：直接在聊天窗口中输入车站编号数字（如：253）。\n\n";
        $response_content .= "3.根据名称搜索：直接在聊天窗口中输入车站名称或地点（如：金融街、新源大街、天宫院）。\n\n";
        $response->setContent($response_content);
        return $response;
    }
}
