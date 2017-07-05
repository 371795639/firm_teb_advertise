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
        $dbFlow     = D('Flow');
        $moneyNotice= $dbFlow -> get_flow_by_uid($_SESSION['userid'],'select');
        foreach($moneyNotice as $k => $v){
            $type = $moneyNotice[$k]['type'];
            switch($type){
                case 1:
                    $newType = '完成任务';
                    break;
                case 2:
                    $newType = '推荐用户';
                    break;
                case 3:
                    $newType = '推荐代理商';
                    break;
                case 4:
                    $newType = '玩家充值';
                    break;
                case 5:
                    $newType = '提现';
                    break;
                case 6:
                    $newType = '兑换中心';
                    break;
                case 7:
                    $newType = '注册缴费';
                    break;
            }
            $moneyNotice[$k]['i_type'] = $newType;
        }
        $this -> assign('moneyNotice',$moneyNotice);
        $this -> display();
    }

























}
