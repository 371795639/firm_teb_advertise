<?php

namespace Home\Controller;

class WeeklySettleController{

    /**任务结算**/
    public function taskSettle(){
        $dbStaff        = D('Staff');
        $dbTaskDone     = D('TaskDone');
        $dbParameter    = D('Parameter');
        $date           = date('Y-m-d H:i:s');
        $uids           = $dbTaskDone   -> get_time_in_last_week($date,'','uid');           //获取已完成上周日常任务的所有用户ID
        $uidAll         = $dbStaff      -> get_all_staff_key('id');                         //获取所有加盟商的ID
        $uidUnset       = i_array_unique($uidAll,$uids);                                    //未完成上周日常任务的所有用户ID
        error_log(date("[Y-m-d H:i:s]").'上周完成日常任务的用户ID:'.print_r($uids,1),3,"/data/tuiguang/logs/taskSettle.log");
        error_log(date("[Y-m-d H:i:s]").'上周领取任务的用户ID:'.print_r($uidAll,1),3,"/data/tuiguang/logs/taskSettle.log");
        error_log(date("[Y-m-d H:i:s]").'上周未完成任务的用户ID:'.print_r($uidUnset,1),3,"/data/tuiguang/logs/taskSettle.log");
        $parameter      = $dbParameter -> get_parameter_by_id('3');
        $discount       = $parameter['value'] / 100;
        foreach($uidAll as $k => $v){
            $uidAl = $uidAll[$k];
            //获取分红金额
            $bounsMoney = 100;



            //分红发放状态
            $bounsStatus    = pay_week_bonus($uidAl);
            if($bounsStatus == 1){
                $flowArr[]          = array();
                $staffArr[]         = array();
                $noticeArr[]        = array();
                $rewardDailyArr[]   = array();
            }else{
                $staffArr[]         = array();
                //流水表 flow
                $dataFlow   = array(
                    'uid'           => $uidAl,
                    'type'          => 1,
                    'money'         => $bounsMoney,
                    'order_id'      => make_orderId(),
                    'create_time'   => date('Y-m-d 5:21:21'),
                );
                $flowArr[]  = array(
                    'id'    => $uidAl,
                    'data'  => $dataFlow,
                );
                //奖励表 reward  日常任务奖励
                $dataRewardDaily = array(
                    'uid'           => $uidAl,
                    'type'          => 1,       //日常任务奖励
                    'money'         => $bounsMoney * (1 - $discount),
                    'game_coin'     => $bounsMoney * $discount,
                    'order_id'      => make_orderId(),
                    'create_time'   => date('Y-m-d 5:21:21'),
                    'remarks'       => "获得分红奖励 $bounsMoney 元",
                );
                $rewardDailyArr[]   = array(
                    'id'    => $uidAl,
                    'data'  => $dataRewardDaily,
                );
                //通知表 notice
                $dataNotice = array(
                    'uid'           => $uidAl,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> 3,
                    'notice_title'  => '未完成上周任务',
                    'create_time'   => date('Y-m-d 5:21:21'),
                    'notice_content'=> "获得分红奖励 $bounsMoney 元",
                );
                $dataNotices = array(
                    'uid'           => $uidAl,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> 4,
                    'notice_title'  => '未完成上周任务',
                    'create_time'   => date('Y-m-d 5:21:21'),
                    'notice_content'=> "获得分红奖励 $bounsMoney 元",
                );
                $noticeArr[]= array(
                    'id'    => $uidAl,
                    'data'  => $dataNotice,
                );
                $noticeArr[]= array(
                    'id'    => $uidAl,
                    'data'  => $dataNotices,
                );
            }
        }
        payReward($staffArr,$rewardDailyArr,$flowArr,$noticeArr);
    }
}