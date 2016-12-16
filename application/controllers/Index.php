<?php
/**
 * 话题首页
 *
 * @package    Controller
 * @author     李枨煊 <chengxuan@staff.weibo.com>
 */
class IndexController extends Yaf_Controller_Abstract {

    /**
     * 首页
     *
     * @return void
     */
    public function indexAction() {
    
        $this->getView()->assign(array(
            'js_config' => array(
                'baidu_ak'  => 'G71gYxWBYGyGLZNKivq58Yu9',
            ),
        ));
    }

}