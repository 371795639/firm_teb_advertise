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
        $this -> assign('sysNotice',$sysNotice);
        $this -> display();
    }


    /**任务消息**/
    public function taskNotice(){
        $dbNotice   = D('Notice');
        $taskNotice  = $dbNotice -> get_notice_by_type('4');
        $this -> assign('taskNotice',$taskNotice);
        $this -> display();
    }


    /**活动消息**/
    public function eventNotice(){
        $dbNotice   = D('Notice');
        $eventNotice  = $dbNotice -> get_notice_by_type('2');
        $this -> assign('eventNotice',$eventNotice);
        $this -> display();
    }


    /**资金变动**/
    public function moneyNotice(){
<<<<<<< HEAD

=======
>>>>>>> e8f540f214c4ccf487734a58a013e21efae13564
        $this -> display();
    }

























}
