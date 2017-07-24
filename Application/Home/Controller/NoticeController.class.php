<?php

namespace Home\Controller;
use Think\Controller;

class NoticeController extends HomeController {

    /**消息首页**/
    public function index(){
        $dbNotice   = D('Notice');
        $sysCount   = $dbNotice -> count_notice_by_type('1',0,'1');
        $taskSys    = $dbNotice -> count_notice_by_type('4',0,'1');
        $taskOwn    = $dbNotice -> count_notice_by_type('4',$_SESSION['userid'],'2');
        $taskCount  = $taskSys + $taskOwn;
        $eventCount = $dbNotice -> count_notice_by_type('2',0,'1');
        $moneyCount = $dbNotice -> count_notice_by_type('3',$_SESSION['userid'],'2');
        $this -> assign('sysCount',$sysCount);
        $this -> assign('taskCount',$taskCount);
        $this -> assign('eventCount',$eventCount);
        $this -> assign('moneyCount',$moneyCount);
        $this -> display('Notice/index');
    }


    /**系统公告**/
    public function sysNotice(){
        $dbNotice   = D('Notice');
        $sysNotice  = $dbNotice -> get_notice_by_type_time_format('1',0,'1');
        $dbNotice   -> set_is_read('1',0,'1');
        $this -> assign('sysNotice',$sysNotice);
        $this -> display('Notice/sysNotice');
    }


    /**任务消息**/
    public function taskNotice(){
        $dbNotice   = D('Notice');
        $taskOwn    = $dbNotice -> get_notice_by_type_time_format('4',$_SESSION['userid'],2);      //任务结算时的任务消息
        $taskSystem = $dbNotice -> get_notice_by_type_time_format('4',0,1);                        //管理员发布的任务消息
        if(empty($taskOwn)){
            $taskNotice = $taskSystem;
        }elseif(empty($taskSystem)){
            $taskNotice = $taskOwn;
        }elseif(empty($taskOwn) && empty($taskSystem)){
            $taskNotice = null;
        }else{
            $taskNotice = array_merge($taskOwn,$taskSystem);
        }
        $dbNotice   -> set_is_read('4',$_SESSION['userid'],'2');
        $dbNotice   -> set_is_read('4',0,'1');
        $this -> assign('taskNotice',$taskNotice);
        $this -> display('Notice/taskNotice');
    }


    /**活动消息**/
    public function eventNotice(){
        $dbNotice   = D('Notice');
        $eventNotice= $dbNotice -> get_notice_by_type_time_format('2',0,'1');
        $dbNotice   -> set_is_read('2',0,'1');
        $this -> assign('eventNotice',$eventNotice);
        $this -> display('Notice/eventNotice');
    }


    /**资金变动**/
    public function moneyNotice(){
        $dbNotice   = D('Notice');
        $moneyNotice= $dbNotice -> get_notice_by_type_time_format('3',$_SESSION['userid'],'2');
        $dbNotice   -> set_is_read('3',$_SESSION['userid'],'2');
        $this -> assign('moneyNotice',$moneyNotice);
        $this -> display('Notice/moneyNotice');
    }

























}
