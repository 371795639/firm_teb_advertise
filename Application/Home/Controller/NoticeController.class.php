<?php

namespace Home\Controller;
use Think\Controller;

class NoticeController extends HomeController {

    /**消息首页**/
    public function index(){

        $this -> display();
    }

//任务模块是否完成游戏任务和充值业绩等待商议结果，现在开始做消息模块

    /**默认消息展示页面**/
    public function noticeDetail(){

        $this -> display();
    }


    /**系统公告**/
    public function sysNotice(){
        $dbNotice = D('Notice');
        $sysNotice = $dbNotice -> get_notice_by_type('1');
        $date = date('Y-m-d');
        foreach($sysNotice as $k => $v){
            $sysNotice[$k]['new_time'] = strtotime($sysNotice[$k]['create_time']);
            $sysNotice[$k]['new_time'] = date('Y-m-d',$sysNotice[$k]['new_time']);
        }
        $this -> assign('date',$date);
        $this -> assign('sysNotice',$sysNotice);
        $this -> display();
    }







}
