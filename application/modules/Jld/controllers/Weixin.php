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
        $config_weixin = new \Yaf_Config_Ini(APP_PATH . 'conf/weixin_jld.ini');
        $echostr = filter_input(INPUT_GET, 'echostr');
        $weixin = new \Apilib\Weixincallback();
        if($echostr) {
            echo $echostr;
        } else {
            $wxcrypt = new Apilib\Wxcrypt($config_weixin->api);
            $request_str = $wxcrypt->decodeRequest();
            \Comm\Debug::log($request_str);
            $request = $weixin->getRequestMsg($request_str);
//            \Comm\Debug::log(print_r($request, true));
            if($request instanceof \Dataobject\Weixin\Requestmsg\Location) {
                //处理地理位置
            } elseif ($request instanceof \Dataobject\Weixin\Requestmsg\Text) {
                //处理文本
                $response = $this->_text($request);
            } elseif ($request instanceof \Dataobject\Weixin\Requestmsg\Event && $request->Event === 'subscribe') {
                //关注事件
                $response = $this->_text($request);
            } elseif ($request instanceof \Dataobject\Weixin\Requestmsg\Image) {
                //处理图片消息
                $response = $this->_image($request);
            }

            if($response instanceof \Dataobject\Weixin\Responsemsg\Base) {
                echo $wxcrypt->encode($weixin->responseMsg($response));
            }
        }
    }
    
    /**
     * 处理图片数据
     * @param \Dataobject\Weixin\Requestmsg\Text $request
     * @return \Dataobject\Weixin\Responsemsg\Text
     */
    protected function _image(\Dataobject\Weixin\Requestmsg\Image $request) {
        $from_user = addslashes($request->FromUserName);
        $pic_url = addslashes($request->PicUrl);
        $create_time = addslashes($request->CreateTime);
        $sql = "INSERT INTO jld_wxpic (from_user, pic_url, create_time) VALUES ('{$from_user}', '{$pic_url}', '{$create_time}')";
        $mysql = new SaeMysql();
        $mysql->runSql($sql);
        
        $response = new Dataobject\Weixin\Responsemsg\Text($request);
        $response_content = "图片已收到";
        $response->setContent($response_content);
        return $response;
    }


    /**
     * 处理文本数据
     *
     * @param \Dataobject\Weixin\Requestmsg\Text $request
     */
    protected function _text(\Dataobject\Weixin\Requestmsg\Base $request) {
        $response = new Dataobject\Weixin\Responsemsg\Text($request);
        $response_content = "金利达电脑：\n";
        $response_content .= "打印照片请直接发送照片至本公众账号（使用原图尺寸）。\n\n";
        $response_content .= "地址：内蒙古巴彦淖尔市临河区胜利北路区法院立案大厅对面。\n";
        $response_content .= "电话：13614886248\n";
        $response_content .= "传真：0478-8277088\n";
        $response->setContent($response_content);
        return $response;
    }
}
