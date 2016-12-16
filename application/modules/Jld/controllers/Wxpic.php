<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WxpicController extends Yaf_Controller_Abstract {

    public function init() {
        //密码验证
        if (empty($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'youqinglangzi' || $_SERVER['PHP_AUTH_PW'] != '197048') {
            header('WWW-Authenticate: Basic realm="JLD Weixin Pic"');
            header('HTTP/1.0 401 Unauthorized');
            exit('Auth Failed.');
        }
    }

    public function showAction() {
        $sql = "SELECT * FROM jld_wxpic ORDER BY id DESC LIMIT 100";
        $mysql = new SaeMysql();
        $result = $mysql->getData($sql);
        $this->getView()->assign(array('result' => $result));
    }

}
