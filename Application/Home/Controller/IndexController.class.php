<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends Controller {

	//前台默认首页
    public function index(){
        //重定向路由，指向登录页
        $this -> redirect('Login/login');
    }


    /**接口授权**/
    public function getApi(){
        header('Content-Type: text/html; charset=utf-8');
        /*授权*/
        $url = "http://119.23.60.80/admin/napp";
        $post_data = "api=auth&username=admin&passworld=admin123";
        $cookie_file = '/data/tuiguang/cookie/cookie.txt';
        //获取cookies并保存
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//返回字符串，而非直接输出
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);//存储cookies
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}