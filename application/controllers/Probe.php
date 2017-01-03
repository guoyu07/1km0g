<?php
/**
 * 探针
 *
 * @package Controller
 * @author  chengxuan <chengxuan@staff.weibo.com>
 */
class ProbeController extends Yaf_Controller_Abstract {
    /**
     * 初始化控制器方法
     *
     * @see Yaf_Controller_Abstract::init()
     */
    public function init() {

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
        phpinfo();
    }
}
