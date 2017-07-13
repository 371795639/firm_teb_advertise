<?php

namespace Home\Controller;

class WeeklySettleController{

    /**任务结算**/
    public function taskSettle(){
        $dbFlow         = M('Flow');
        $dbStaff        = D('Staff');
        $dbReward       = M('Reward');
        $dbNotice       = M('Notice');
        $taskDone       = D('TaskDone');
        $dbTaskDone     = D('TaskDone');
        $dbParameter    = M('Parameter');
//        $dbTaskWeekly   = D('TaskWeekly');
        $date           = ('Y-m-d H:i:s');
        $uids           = $dbTaskDone -> get_time_in_last_week($date,'uid');    //获取已完成上周日常任务的uid
        $date           = date('Y-m-d H:i:s');
        $monday         = get_last_monday($date);
        $sunday         = get_last_sunday($date);
        //根据uid中加盟商的等级，获取日常任务的总金额
        foreach($uids as $k => $v) {
            $id = $uids[$k];
//            $class      = $dbStaff  -> get_staff_league($id);
            $doneDaily  = $taskDone -> get_last_week_task($id,'1');             //上周的日常任务
            $doneExtra  = $taskDone -> get_last_week_task($id,'2');             //上周的额外任务
            $parameter  = $dbParameter -> where("id = 3") -> find();
            if (empty($doneDaily)) {
                $moneyDaily = 0;
            }else{
//                $dailMoney = $dbTaskWeekly -> get_weekly_money('1', $class);      //日常任务总金额
                $where  = array(
                    'uid'       => $id,
                    'task_id'   => 0,
                    'status'    => 8,
                    'get_time'  => array(array('gt', $monday), array('lt', $sunday)),
                );
                $moneyDetail    = $dbTaskDone -> where($where) -> find();
                $moneyDetails   = explode(',',$moneyDetail['reward']);
                $moneyDaily     = $moneyDetails[0];
                if (empty($doneExtra)) {
                    $moneyExtra = 0;
                }else{
                    $moneyExtra = $moneyDetails[1] + $moneyDetails[2];
                }
            }
            if($moneyDaily == 0) {
                $dateNotice = array(
                    'uid'           => $id,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> '3',
                    'notice_title'  => '上周任务未完成',
                    'notice_content'=> "您上周任务未完成，这周要加油喽。",
                );
                $dbNotice -> add($dateNotice);
            }else{
                $totalMoney = $moneyDaily + $moneyExtra;
                //staff表 =>income = $totalMoney * $discount;money = $totalMoney * (1 - $discount);
                $oldData = $dbStaff -> get_staff_by_id($id);
                $discount = $parameter['value'] / 100;
                $dataStaff = array(
                    'income'    => $oldData['income'] + ($totalMoney * $discount),
                    'money'     => $oldData['money'] + $totalMoney * (1 - $discount),
                );
                $dbStaff -> save_staff_by_id($id, $dataStaff);
                //流水表 flow
                $dataFlow = array(
                    'uid'           => $id,
                    'type'          => 1,
                    'money'         => $totalMoney,
                    'order_id'      => 0,
                    'create_time'   => date('Y-m-d H:i:s'),
                );
                $dbFlow -> add($dataFlow);
                //奖励表 reward
                $dataRewardDaily = array(
                    'uid'           => $id,
                    'type'          => 1,       //日常任务奖励
                    'base_money'    => $moneyDaily,
                    'extra_money'   => 0,
                    'game_coin'     => 0,
                    'order_id'      => 0,
                    'create_time'   => date('Y-m-d H:i:s'),
                    'remarks'       => "完成上周任务，奖励总金额 $totalMoney 元",
                );
                $dbReward -> add($dataRewardDaily);
                $dataRewardExtra = array(
                    'uid'           => $id,
                    'type'          => 2,       //额外任务奖励
                    'base_money'    => 0,
                    'extra_money'   => $moneyExtra,
                    'game_coin'     => 0,
                    'order_id'      => 0,
                    'create_time'   => date('Y-m-d H:i:s'),
                    'remarks'       => "完成上周任务，奖励总金额 $totalMoney 元",
                );
                $dbReward -> add($dataRewardExtra);
                //通知表 notice
                $dataNotice = array(
                    'uid'           => $id,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> '3',
                    'notice_title'  => '恭喜您已完成上周任务',
                    'notice_content'=> "获得上周任务总金额 $totalMoney 元",
                );
                $dbNotice -> add($dataNotice);
            }
        }
    }
}
