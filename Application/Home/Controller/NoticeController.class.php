<?php

namespace Home\Controller;
use Think\Controller;

class NoticeController extends HomeController {

    /**消息首页**/
    public function index(){
        $dbNotice   = D('Notice');
        $sysCount   = $dbNotice -> count_notice_by_type('1');
        $taskCount  = $dbNotice -> count_notice_by_type('4');
        $eventCount = $dbNotice -> count_notice_by_type('2');
        $this -> assign('sysCount',$sysCount);
        $this -> assign('taskCount',$taskCount);
        $this -> assign('eventCount',$eventCount);
        $this -> display();
    }


    /**系统公告**/
    public function sysNotice(){
        $dbNotice   = D('Notice');
        $sysNotice  = $dbNotice -> get_notice_by_type_time_format('1');
        $dbNotice   -> set_is_read($sysNotice,'1');
        $this -> assign('sysNotice',$sysNotice);
        $this -> display();
    }


    /**任务消息**/
    public function taskNotice(){
        $dbNotice   = D('Notice');
        $taskNotice = $dbNotice -> get_notice_by_type_time_format('4');
        $dbNotice   -> set_is_read($taskNotice,'4');
        $this -> assign('taskNotice',$taskNotice);
        $this -> display();
    }


    /**活动消息**/
    public function eventNotice(){
        $dbNotice   = D('Notice');
        $eventNotice= $dbNotice -> get_notice_by_type_time_format('2');
        $dbNotice   -> set_is_read($eventNotice,'2');
        $this -> assign('eventNotice',$eventNotice);
        $this -> display();
    }


    /**资金变动**/
    public function moneyNotice(){
        //资金变动
        $this -> display();
    }

























}
