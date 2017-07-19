<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}


/**
 * 时间格式化
 * @param  string $date
 * @return string 完整的时间显示
 */
function time_formats($date,$format='m-d H:i'){
    if($date == 0){
        $re = '-';
    }else{
        $time = strtotime($date);
        $re = date($format, $time);
    }
    return $re;
}

/**
 * 时间格式化
 * @param  string $date
 * @return string 完整的时间显示
 */
function time_formatss($date,$format='y年m月d日'){
    if($date == 0){
        $re = '-';
    }else{
        $time = strtotime($date);
        $re = date($format, $time);
    }
    return $re;
}

/**
 * 时间格式化
 * @param  string $date
 * @return string 完整的时间显示
 */
function time_formatiss($date,$format='Y-m-d'){
    if($date == 0){
        $re = '-';
    }else{
        $time = strtotime($date);
        $re = date($format, $time);
    }
    return $re;
}


/**
 * 时间格式化
 * @param  string $date
 * @return string 完整的时间显示
 */
function time_formatsss($date,$format='H:i'){
    if($date == 0){
        $re = '-';
    }else{
        $time = strtotime($date);
        $re = date($format, $time);
    }
    return $re;
}


/**
 * 根据$date获取上周一
 * @param $date string  时间
 * @return bool|string  当前时间的上周一
 */
function get_last_monday($date){
    if(!empty($date)) {
        $time = strtotime($date);
        $week = date('N', $time);
        if ($week == 1) {
            $start_time = date('Y-m-d 02:00:00', strtotime('-1 monday', $time));
        } else {
            $start_time = date('Y-m-d 02:00:00', strtotime('-2 monday', $time));
        }
    }else{
        $start_time = "";
    }
    return $start_time;
}


/**
 * 根据$date获取上周末
 * @param $date string  时间
 * @return bool|string  当前时间的上周末
 */
function get_last_sunday($date){
    if(empty($date)) {
        $end_time = "";
    }else{
        $start_time = get_last_monday($date);
        $ss         = strtotime($start_time);
        $end_time   = date('Y-m-d 23:59:59', strtotime('Sunday', $ss));
    }
    return $end_time;
}


/**
 * 根据$date获取上上周一
 * @param $date string  时间
 * @return bool|string  当前时间的上周一
 */
function get_last_last_monday($date){
    if(!empty($date)) {
        $time = strtotime($date);
        $week = date('N', $time);
        if ($week == 1) {
            $start_time = date('Y-m-d 02:00:00', strtotime('-2 monday', $time));
        } else {
            $start_time = date('Y-m-d 02:00:00', strtotime('-3 monday', $time));
        }
    }else{
        $start_time = "";
    }
    return $start_time;
}


/**
 * 根据$date获取上上周末
 * @param $date string  时间
 * @return bool|string  当前时间的上周末
 */
function get_last_last_sunday($date){
    if(empty($date)) {
        $end_time = "";
    }else{
        $start_time = get_last_last_monday($date);
        $ss         = strtotime($start_time);
        $end_time   = date('Y-m-d 23:59:59', strtotime('Sunday', $ss));
    }
    return $end_time;
}


/**两数组中去除重复的元素
 * @param $arr1
 * @param $arr2
 */
function i_array_unique($arr1,$arr2){
    foreach ($arr1 as $k=>$v) {
        foreach($arr2 as $kk=>$vv){
            if($v == $vv){
                unset($arr1[$k]);//删除$a数组同值元素
                unset($arr2[$kk]);//删除$b数组同值元素
            }
        }
    }
    return $arr1;
}
