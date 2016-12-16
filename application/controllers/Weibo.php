<?php

/**
 * 微博粉服拉口
 *
 * @package    Controller
 * @author     李枨煊 <chengxuan@staff.weibo.com>
 */
class WeiboController extends Yaf_Controller_Abstract {

    /**
     * 初始化控制器方法
     *
     * @see Yaf_Controller_Abstract::init()
     */
    public function init() {

        //设置应用类别
        Yaf_Registry::set('application_type', 'weibo');

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
        $request = $this->getRequest();

        // 初始化SDK
        $weibo_cb = Apilib\Weibocallback::init();

        // 首次验证url时会有'echostr'参数，后续推送消息时不再有'echostr'字段
        // 若存在'echostr'说明是首次验证,则返回'echostr'的内容。
        if($request->getQuery('echostr')) {
            $this->_echostr();
        } else {
            // 处理开放平台推送来的消息,首先获取推送来的数据.
            $post_msg = $weibo_cb->getPostMsgStr();
            if(empty($post_msg['data']['longitude']) || empty($post_msg['data']['latitude'])) {
                $text = '请用微博客户端打开私信对话框，点击右下角的+号，再点“位置”，即可查询附近的自行车位。本功能目前处于测试阶段，如有问题，请勿骂娘。';
                $weibo_cb->replyText($text, true);
            } else {
                //修正坐标
                $coords = Comm\Geo::gcj2baidu($post_msg['data']['longitude'], $post_msg['data']['latitude']);
//                $coords = Apilib\Baidumap::coords("{$post_msg['data']['longitude']},{$post_msg['data']['latitude']}");
                if($coords) {
                    $location_x = $coords['lat'];
                    $location_y = $coords['lng'];
                } else {
                    $location_x = $post_msg['data']['longitude'];
                    $location_y = $post_msg['data']['latitude'];
                }

                $map_url = 'http://' . filter_input(INPUT_SERVER, 'HTTP_APPNAME') . ".sinaapp.com/index.php/map/near?lat={$location_x}&lng={$location_y}&from=weibo";
                $station = BikeModel::showNearBike($location_x, $location_y, 3);
                $articles = array();
                $i = 0;
                foreach($station as $value) {
                    $display_name = sprintf(
                        "%u.%s：%.02fkm\n【剩】%u【空】%u",
                        $value['id'],
                        $value['name'],
                        $value['lat_lng_size'] * 111,
                        $value['availBike'],
                        $value['capacity'] - $value['availBike']
                    );

                    if($i === 0) {
                        $width = 280;
                        $height = 155;
                    } else {
                        $width = $height = 64;
                    }
                    $image = 'http://api.map.baidu.com/staticimage?' . http_build_query(array(
                        'zoom'      => '14',
                        'center'    => "{$value['lat']},{$value['lng']}",
                        'width'     => $width,
                        'height'    => $height,
                        'markers'   => "{$value['lat']},{$value['lng']}",
                        'copyright' => '1',

                    ));

                    //李枨煊做灰度（暂时去掉灰度）
                    if(true || $post_msg['sender_id'] == '1150169277') {
                        $map_thumb = BikeModel::showMapThumb($value['lat'], $value['lng'], $width, $height);
                        if($map_thumb['img']) {
                            $image = 'http://1km0g-bike.stor.sinaapp.com/' . $map_thumb['img'];
                        }
                    }

                    $articles[] = array(
                        'display_name'  => $display_name,
                        'summary'       => $value['address'],
                        'image'         => $image,
                        'url'           => $map_url . '&bikeid=' . $value['id'],
                    );
                    ++$i;
                }

                //追加一个查看全部
                $articles[] = array(
                    'display_name'  => '查看全部车位',
                    'summary'       => '点击查看全部车位',
                    'image'         => '',
                    'url'           => $map_url . '&showall=1',
                );

                //$text = "您的位置：{$post_msg['data']['longitude']},{$post_msg['data']['latitude']}";
                $weibo_cb->replyArticle($articles, true);
            }
        }
    }


    /**
     * 首次验证url验证'echostr'参数
     */
    protected function _echostr() {
        echo $this->getRequest()->getQuery('echostr');
    }
}