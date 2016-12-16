<?php

/**
 * 错误控制器
 *
 * @package    Controller
 * @author     Chengxuan <chengxuan@staff.sina.com.cn>
 */
class ErrorController extends Yaf_Controller_Abstract {

    //是否是AJAX请求
    protected $_is_ajax = false;

    /**
     * 处理异常
     * @param Exception $exception
     * @return boolean
     */
    public function errorAction($exception) {
        $request = Yaf_Dispatcher::getInstance()->getRequest();
        $this->_is_ajax = $request->isXmlHttpRequest();

        if($exception instanceof ErrorException) {
            $this->debug($exception);
        } elseif($exception instanceof Exception_Nologin) {
            if ($this->is_ajax) {
                $this->showError($exception, '100002', 'no login');
            } else {
                header('Location:' . Comm_Config::get('app.site.weibo') . 'login.php?url=' . urlencode('http://' . $request->getServer('HTTP_HOST') . $request->getServer('REQUEST_URI')));
            }
        } elseif($exception instanceof Yaf_Exception_LoadFailed) {
            $code = 303404;
            $msg = $exception->getMessage();
            $this->showError($exception, $code, $msg, '3');
        } elseif($exception instanceof Exception_System) {
            $code = $exception->getCode();
            $msg =  $exception->getMessage();
            $this->showError($exception, $code, $msg, '1');
        } else {
            $this->showError($exception, null, null, '2');
        }

        return false;
    }

    /**
     * 显示错误
     * @param 	Exception 	$e
     * @param 	int 		$code
     * @param 	string 		$msg
     * @param	int    		$style	样式（1.放屁，2.想象，3.沙漠）
     */
    protected function showError(Exception $e, $code = null, $msg = null, $style = '2') {
        
        switch (Yaf_Registry::get('application_type')) {
            case 'weibo' :
                $this->_showErrorWeibo($e, $code, $msg, $style);
                break;
            
            default :
                $this->_showErrorHtml($e, $code, $msg, $style);
        }
        

    }
    
    protected function _showErrorWeibo(Exception $e, $code = null, $msg = null, $style = '2') {
        $text = '出错啦！[' . $e->getCode() . ']' . $e->getMessage();
        $weibo_cb = Apilib\Weibocallback::init();
        $weibo_cb->replyText($text, true);
    }
    
    protected function _showErrorHtml(Exception $e, $code = null, $msg = null, $style = '2') {
        $code === null && $code = $e->getCode();
        $msg === null && $msg = $e->getMessage();
        if (method_exists($e, 'getMetadata')) {
            $metadata = $e->getMetadata();
        } else {
            $metadata = array();
        }
        
        if ($this->is_ajax) { //AJAX处理
            header("Content-type: application/json");
        
            $response = array(
                'code' => $code,
                'msg' => $msg,
                'data' => array(),
            );
        
            //附加调试信息
            /*
            if (Helper_Debug::isDebug()) {
            $response['_debug']['code'] = $e->getCode();
            $response['_debug']['message'] = $e->getMessage();
            $response['_debug']['file'] = $e->getFile() . ' (' . $e->getLine() . ')';
            $response['_debug']['trace'] = explode("\n", $e->getTraceAsString());
            if ($e instanceof Exception_Abstract) {
            $response['_debug']['metadata'] = $e->getMetadata();
            }
            $metadata && $response['_debug']['metadata'] = $metadata;
            }*/
        
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {    //页面处理
            /*
            if (Helper_Debug::isDebug()) {
            Helper_Debug::error($e, FirePHP::WARN);
            if ($e instanceof Exception_Abstract) {
                Helper_Debug::error(array(
                 'code' => $code,
                 'msg' => $msg,
                 'metadata' => $metadata,
                ), 'Exception_info', FirePHP::WARN);
                }
                }*/
        
                $error_page_time = isset($metadata['error_page_time']) ? $metadata['error_page_time'] : 5;
                $this->display('error', array(
                //'err_style'			=> $style,
                'msg' => $msg,
                'code' => $code,
                'error_page_time' => $error_page_time,
            ));
            }
    }

    /**
     * 显示调试信息
     * @param Exception $exception
     * @return boolean
     */
    public function debug($exception) {
        try {
            $type = get_class($exception);
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $exception_txt = Helper_Error::exceptionText($exception);

            $trace = $exception->getTrace();
            if ($exception instanceof ErrorException) {
                // 替换为human readable
                $code = Helper_Error::showType($code);

                if (version_compare(PHP_VERSION, '5.3', '<')) {
                    // 修复php 5.2下关于getTrace的bug
                    //@TODO bug url
                    for ($i = count($trace) - 1; $i > 0; --$i) {
                        if (isset($trace[$i - 1]['args'])) {
                            $trace[$i]['args'] = $trace[$i - 1]['args'];

                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }

            $this->getView()->assign(array(
                'type' => $type,
                'code' => $code,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'exception_txt' => $exception_txt,
                'trace' => $trace,
            ));
            $this->display('debug');
        } catch (Exception $exception) {
            var_dump($exception);
            return false;
        }
    }

}