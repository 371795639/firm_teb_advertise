<?php

namespace Home\Controller;

class WeeklySettleController{

    /**任务结算**/
    public function taskSettle(){
        $dbStaff        = D('Staff');
        $dbTaskDone     = D('TaskDone');
        $dbParameter    = D('Parameter');
        $dbStaffInfo    = D('StaffInfo');
        $date           = date('Y-m-d H:i:s');
        $uids           = $dbTaskDone   -> get_time_in_last_week($date,'','uid');           //获取已完成上周日常任务的所有用户ID
        $taskDones      = $dbTaskDone   -> get_time_in_last_week($date,'','select');        //获取已完成上周日常任务列表
        $uidAll         = $dbStaff      -> get_all_staff_key('id');                         //获取所有加盟商的ID
        $uidUnset       = i_array_unique($uidAll,$uids);                                    //未完成上周日常任务的所有用户ID
        error_log(date("[Y-m-d H:i:s]").'上周完成日常任务的用户ID:'.print_r($uids,1),3,"/data/tuiguang/logs/taskSettle.log");
        error_log(date("[Y-m-d H:i:s]").'上周领取任务的用户ID:'.print_r($uidAll,1),3,"/data/tuiguang/logs/taskSettle.log");
        error_log(date("[Y-m-d H:i:s]").'上周未完成任务的用户ID:'.print_r($uidUnset,1),3,"/data/tuiguang/logs/taskSettle.log");
        foreach($uidUnset as $k => $v){
            $uidUnsets      = $uidUnset[$k];
            //未完成任务的用户扣除信用分
            $infoCredit     = $dbStaffInfo -> get_staff_by_uid($uidUnsets);
            $infoCreditNum  = $infoCredit['credit_num'] - 1;
            $infoCreditNum  = $infoCreditNum <= -5 ? -5 : $infoCreditNum;
            $infoCreditValue= $dbStaffInfo -> get_credit($infoCreditNum);
            $infoCred       = array(
                'credit_value'  => $infoCreditValue,
                'credit_num'    => $infoCreditNum,
            );
            $dbStaffInfo    -> save_staff_by_uid($uidUnsets,$infoCred);
            //任务未完成只发分红奖励
            $oldData        = $dbStaff -> get_staff_by_id($uidUnsets);
            $parameter      = $dbParameter -> get_parameter_by_id('3');
            $parameterBase  = $dbParameter -> get_parameter_by_id('5');
            $bounsMoney     = bonus_personal($uidUnsets,'1',$parameterBase['value']);
            $bounsMoney     = $bounsMoney/0.8;
            $discount       = $parameter['value'] / 100;
            $dataStaff  = array(
                'consume_coin'  => $oldData['consume_coin'] + ($bounsMoney * $discount),
                'money'         => $oldData['money'] + $bounsMoney * (1 - $discount),
                'income'        => $oldData['income'] + $bounsMoney,
            );
            $staffArr[] = array(
                'id'    => $uidUnsets,
                'data'  => $dataStaff,
            );
            //流水表 flow
            $dataFlow   = array(
                'uid'           => $uidUnsets,
                'type'          => 1,
                'money'         => $bounsMoney,
                'order_id'      => make_orderId(),
                'create_time'   => date('Y-m-d H:i:s'),
            );
            $flowArr[]  = array(
                'id'    => $uidUnsets,
                'data'  => $dataFlow,
            );
            //奖励表 reward  日常任务奖励
            $dataRewardDaily = array(
                'uid'           => $uidUnsets,
                'type'          => 1,       //日常任务奖励
                'money'         => $bounsMoney * (1 - $discount),
                'game_coin'     => $bounsMoney * $discount,
                'order_id'      => make_orderId(),
                'create_time'   => date('Y-m-d H:i:s'),
                'remarks'       => "未完成上周日常任务，获得分红奖励 $bounsMoney 元",
            );
            $rewardDailyArr[]   = array(
                'id'    => $uidUnsets,
                'data'  => $dataRewardDaily,
            );
            //通知表 notice
            $dataNotice = array(
                'uid'           => $uidUnsets,
                'kind'          => '2',
                'poster'        => 'system',
                'notice_type_id'=> 3,
                'notice_title'  => '未完成上周任务',
                'notice_content'=> "未完成上周日常任务，获得分红奖励 $bounsMoney 元",
            );
            $dataNotices = array(
                'uid'           => $uidUnsets,
                'kind'          => '2',
                'poster'        => 'system',
                'notice_type_id'=> 4,
                'notice_title'  => '未完成上周任务',
                'notice_content'=> "未完成上周日常任务，获得分红奖励 $bounsMoney 元",
            );
            $noticeArr[]= array(
                'id'    => $uidUnsets,
                'data'  => $dataNotice,
            );
            $noticeArr[]= array(
                'id'    => $uidUnsets,
                'data'  => $dataNotices,
            );
        }
        //对于已完成分享推广专员任务的用户：修改recommend_num值=总值-指标，保留此字段值，用于下次任务
        foreach($taskDones as $k => $v){
            if($taskDones[$k]['name'] == '分享推广专员') {
                $taskInneed = $taskDones[$k]['inneed'];
                $staffInfo  = $dbStaff -> get_staff_by_id($taskDones[$k]['uid']);
                $num        = $staffInfo['recommend_num'];
                $newNum     = $num - $taskInneed;
                $newsNum    = $newNum <= 0 ? 0 : $newNum;
                $newsNums['recommend_num'] = $newsNum;      //TODO: 测试时，为防止数据被减为负值。
                $dbStaff -> save_staff_by_id($taskDones[$k]['uid'], $newsNums);
            }
        }
        //给完成任务的用户发奖励，写通知
        foreach($uids as $k => $v) {
            $id             = $uids[$k];
            $parameter      = $dbParameter -> get_parameter_by_id('3');
            $parameterBase  = $dbParameter -> get_parameter_by_id('5');
            //给完成任务的用户增加信用分
            $infoCredit     = $dbStaffInfo -> get_staff_by_uid($id);
            $infoCreditNum  = $infoCredit['credit_num'] + 1;
            $infoCreditNum  = $infoCreditNum >= 4 ? 4 : $infoCreditNum;
            $infoCreditValue= $dbStaffInfo -> get_credit($infoCreditNum);
            $infoCred       = array(
                'credit_value'  => $infoCreditValue,
                'credit_num'    => $infoCreditNum,
            );
            $dbStaffInfo    -> save_staff_by_uid($id,$infoCred);
            //任务奖励
            $start          = $dbTaskDone -> get_start_time($date);
            $end            = $dbTaskDone -> get_end_time($date);
            $taskMoney      = $dbTaskDone->where(array('uid'=>$id,'status'=>8,'task_id'=>0,'get_time'=>array(array('gt',$start),array('lt',$end))))->getField('reward');
            //分红奖励
            $bounsMoney     = bonus_personal($id,'1',$parameterBase['value']);
            $bounsMoney     = $bounsMoney/0.8;
            //任务总收益
            $totalMoney     = $taskMoney + $bounsMoney;
            //staff表发放奖励 =>income = $totalMoney * $discount;money = $totalMoney * (1 - $discount);
            $discount       = $parameter['value']/100;
            $oldData        = $dbStaff -> get_staff_by_id($id);
            $dataStaff      = array(
                'consume_coin'  => $oldData['consume_coin'] + ($totalMoney * $discount),
                'money'         => $oldData['money'] + $totalMoney * (1 - $discount),
                'income'        => $oldData['income'] + $totalMoney,
            );
            $staffArr[] = array(
                'id'    => $id,
                'data'  => $dataStaff,
            );
            //流水表 flow
            $dataFlow   = array(
                'uid'           => $id,
                'type'          => 1,
                'money'         => $totalMoney,
                'order_id'      => make_orderId(),
                'create_time'   => date('Y-m-d H:i:s'),
            );
            $flowArr[]  = array(
                'id'    => $id,
                'data'  => $dataFlow,
            );
            //奖励表 reward  日常任务奖励
            $dataRewardDaily    = array(
                'uid'           => $id,
                'type'          => 1,       //日常任务奖励
                'money'         => $totalMoney * (1 - $discount),
                'game_coin'     => $totalMoney * $discount,
                'order_id'      => make_orderId(),
                'create_time'   => date('Y-m-d H:i:s'),
                'remarks'       => "完成上周日常任务，奖励总金额 $totalMoney 元",
            );
            $rewardDailyArr[]   = array(
                'id'    => $id,
                'data'  => $dataRewardDaily,
            );
            //通知表 notice
            $dataNotice = array(
                'uid'           => $id,
                'kind'          => '2',
                'poster'        => 'system',
                'notice_type_id'=> 3,
                'notice_title'  => '恭喜您已完成上周任务',
                'notice_content'=> "获得上周任务总金额 $totalMoney 元",
            );
            $dataNotices = array(
                'uid'           => $id,
                'kind'          => '2',
                'poster'        => 'system',
                'notice_type_id'=> 4,
                'notice_title'  => '恭喜您已完成上周任务',
                'notice_content'=> "获得上周任务总金额 $totalMoney 元",
            );
            $noticeArr[]= array(
                'id'    => $id,
                'data'  => $dataNotice,
            );
            $noticeArr[]= array(
                'id'    => $id,
                'data'  => $dataNotices,
            );
        }
        payReward($staffArr,$rewardDailyArr,$flowArr,$noticeArr);
    }
}