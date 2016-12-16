<?php
/**
 * 引导文件
 *
 * 可以通过在配置文件中修改application.bootstrap来变更Bootstrap类的位置.
 *
 * @package Application
 * @author  Chengxuan <chengxuan@staff.sina.com.cn>
 */

class Bootstrap extends Yaf_Bootstrap_Abstract {

    /**
     * 初始配置
     *
     * @param Yaf_Dispatcher $dispatcher 路由对象
     *
     * @throws Yaf_Exception_DispatchFailed 异常
     *
     * @return void
     */
    public function _initConfig(Yaf_Dispatcher $dispatcher) {

        //默认MB编码为utf8
        mb_internal_encoding('utf-8');

        //配置本地类库前缀
        $loader = Yaf_Loader::getInstance();
        $loader->registerLocalNamespace(array('Comm'));

        //脚本启动时间
        define('NOW', isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());
    }


}