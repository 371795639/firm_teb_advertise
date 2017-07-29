<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    /**任务大厅**/
    public function taskOffice(){
        $dbStaff            = D('Staff');
        $class              = $dbStaff      -> get_staff_league($_SESSION['userid']);   //获取当前等陆用户的加盟商等级
        if($class == 0){        //推广专员无任务
            $show           = 1;
            $moneyOne       = null;
            $doneTypeOne    = null;
            $totalMoney     = null;
        }else{
            $show           = $this -> taskSubmit('system');            //自动领取任务
            $taskJudgeArr   = $this -> taskJudge();                              //判断任务是否完成
            $dbTaskDone     = D('TaskDone');
            $dbUserShip     = D('UserShip');
            $dbUserCharge   = D('UserCharge');
            $doneTypeOne    = $dbTaskDone   -> get_doing_task($_SESSION['userid'],'1,2','','4');          //本周内已领取的任务
            $taskCount      = $dbTaskDone   -> get_task_field($_SESSION['userid'],'task_count','field');  //完成任务次数
            //任务奖励
            $inneed         = [];
            foreach($doneTypeOne as $item){
                if($item['name'] == "分享推广专员"){
                    $inneed['rec_num'] = $item['inneed'];
                }
                if($item['name'] == "充值业绩"){
                    $inneed['recharge'] = $item['inneed'];
                }
            }
            $oldData        = $dbStaff -> get_staff_by_id($_SESSION['userid']);
            $service_number = $oldData['service_number'];
            if(($oldData['recommend_num'] >= $taskJudgeArr['taskFiveInneed']) && ($taskJudgeArr['taskFourActual'] >= $taskJudgeArr['taskFourInneed'])){
                $recNum     = $oldData['recommend_num'];
                $recharge   = $taskJudgeArr['taskFourActual'];
            }elseif($oldData['recommend_num'] >= $taskJudgeArr['taskFiveInneed']){
                $recNum     = $oldData['recommend_num'];
                $recharge   = $taskJudgeArr['taskFourInneed'];
            }elseif($taskJudgeArr['taskFourActual'] >= $taskJudgeArr['taskFourInneed']){
                $recNum     = $taskJudgeArr['taskFiveInneed'];
                $recharge   = $taskJudgeArr['taskFourActual'];
            }else{
                $recNum     = $taskJudgeArr['taskFiveInneed'];
                $recharge   = $taskJudgeArr['taskFourInneed'];
            }
            $pay_status     = pay_week_bonus($_SESSION['userid']);
            $taskMoney      = task_reward($_SESSION['userid'],$recNum,$recharge,$service_number,$pay_status);
            //收益总金额
            $theBouns       = $taskMoney['bonus']/0.8;
            $theMoney       = $taskMoney['task'];
            $task_month     = 1;
            $totalMoney     = array(
                'bouns'     => $theBouns,
                'task_this' => $theMoney,
                'task_week' => $theBouns+$theMoney,
                'task_month'=> $task_month,
            );
            //插入结算数据
            $in_data = array(
                'uid'       => $_SESSION['userid'],
                'status'    => 8,
                'reward'    => $theMoney,
                'task_id'   => 0,
                'get_time'  => date('Y-m-d H:i:s'),
                'done_time' => '',  //不可用null，否则无法插入数据
            );
            $select_data = array(
                'uid'       => $_SESSION['userid'],
                'status'    => 8,
                'task_id'   => 0,
            );
            $is_set         = $dbTaskDone -> where($select_data) ->find();
            if(empty($is_set)){
                $dbTaskDone -> add_done($in_data);      //插入结算数据
            }
            //显示任务进度和指标
            $left           = $dbStaff    -> get_staff_by_id($_SESSION['userid']);              //已分享推广专员数量
            $fiveCount      = $left['recommend_num'];
            $twoGameId      = $left['game_id'];
            $oneCount       = $dbUserShip -> get_user_by_recommend($twoGameId, 'count'); //已分享玩家数量
            $users          = $dbUserShip -> get_user_by_recommend($twoGameId,'select');
            if(empty($users)){
                $twoCount   = 0;
                $threeCount = 0;
            }else{
                foreach($users as $k2 => $v2){
                    $game_id[] = $users[$k2]['game_id'];
                }
                $twoCount   = $dbUserCharge -> get_user_charge($game_id,'1','money');          //首充金额
                $threeCount = $dbUserCharge -> get_user_charge($game_id,'2','money');          //充值业绩
            }
            foreach($doneTypeOne as $k => $v){
                if($doneTypeOne[$k]['name']   == '分享推广专员'){
                    $doneTypeOne[$k]['count'] = $fiveCount-$taskCount*$doneTypeOne[$k]['inneed'] <= 0 ? 0 : $fiveCount-$taskCount*$doneTypeOne[$k]['inneed'];
                }
                if($doneTypeOne[$k]['name']   == '分享玩家'){
                    $doneTypeOne[$k]['count'] = $oneCount-$taskCount*$doneTypeOne[$k]['inneed'] <= 0 ? 0 : $oneCount-$taskCount*$doneTypeOne[$k]['inneed'];
                }
                if($doneTypeOne[$k]['name']   == '首充金额'){
                    $doneTypeOne[$k]['count'] = $twoCount-$taskCount*$doneTypeOne[$k]['inneed'] <= 0 ? 0 : $twoCount-$taskCount*$doneTypeOne[$k]['inneed'];
                }
                if($doneTypeOne[$k]['name']   == '充值业绩'){
                    $doneTypeOne[$k]['count'] = $threeCount-$taskCount*$doneTypeOne[$k]['inneed'] <= 0 ? 0 : $threeCount-$taskCount*$doneTypeOne[$k]['inneed'];
                }
            }
        }
        $this -> assign('show',$show);
        $this -> assign('class',$class);
        $this -> assign('totalMoney',$totalMoney);
        $this -> assign('doneTypeOne',$doneTypeOne);
        $this -> display('Task/taskOffice');
    }


    /**
     * 领取任务
     * @param   string  $type     self：用户手动提交；system：系统自动提交
     * @return  int     $result   值为1，未完成全部任务；为2完成全部任务
     */
    public function taskSubmit($type){
        $dbTaskDone     = D('TaskDone');
        $dbTaskWeekly   = D('TaskWeekly');
        $dbStaffInfo    = D('StaffInfo');
        $taskSet        = $dbTaskDone   -> get_week_type_task($_SESSION['userid'],'','1','');
        $class          = D('Staff')    -> get_staff_league($_SESSION['userid']);     //获取当前等陆用户的加盟商等级
        $weeklyTypeOne  = $dbTaskWeekly -> get_weekly_type('1',$class);
        if($taskSet){
            $status = [];
            foreach($taskSet as $k => $v){
                $status[] = $taskSet[$k]['status'];
            }
            if(in_array('1',$status)){           //完成任务
                $result = 1;
            }else{
                $result = 2;
                if($type == 'self'){
                    $doneTypeOn     = $dbTaskDone -> get_doing_task($_SESSION['userid'],1,'','');
                    //如果表中含有状态值为1的不插入，否则插入
                    if(empty($doneTypeOn)){
                        //更新结算数据
                        $rewardMap = array(
                            'uid'       => $_SESSION['userid'],
                            'task_id'   => 0,
                            'status'    => 8,
                        );
                        $rewardData     = array(
                            'get_money' => 1,
                            'done_time' => date('Y-m-d H:i:s'),
                        );
                        $dbTaskDone     -> where($rewardMap) -> save($rewardData);
                        $newUpdateCount = $dbTaskDone -> where($rewardMap) -> setInc('task_count');
                        //再次领取任务
                        $newCount       = $dbTaskDone -> get_task_field($_SESSION['userid'],'task_count','field');
                        $dataTwice      = array(
                            'uid'       => $_SESSION['userid'],
                            'get_time'  => date('Y-m-d H:i:s'),
                            'done_time' => '',  //不可用null，否则无法插入数据
                            'status'    => 1,
                            'reward'    => null,
                            'get_money' => 0,
                            'task_count'=> $newCount,
                        );
                        foreach($weeklyTypeOne as $k => $v){
                            $dataTwice['task_id']    = $weeklyTypeOne[$k]['task_id'];
                            $dataTwice['inneed']     = $weeklyTypeOne[$k]['inneed'];
                            $newAdd     = $dbTaskDone -> add_done($dataTwice);
                        }
                        //增加信用分
                        $infoCredit     = $dbStaffInfo -> get_staff_by_uid($_SESSION['userid']);
                        $infoCreditNum  = $infoCredit['credit_num'] + 1;
                        $infoCreditNum  = $infoCreditNum >= 4 ? 4 : $infoCreditNum;
                        $infoCreditValue= $dbStaffInfo -> get_credit($infoCreditNum);
                        $infoCred       = array(
                            'credit_value'  => $infoCreditValue,
                            'credit_num'    => $infoCreditNum,
                        );
                        $resStaffUpdate = $dbStaffInfo  -> save_staff_by_uid($_SESSION['userid'],$infoCred);
                        //计算任务奖励
                        $totalMoney     = 100;
                        
                        //写入reward表
                        $dbReward       = M('Reward');
                        $rewardDate     = array(
                            'uid'           => $_SESSION['userid'],
                            'lower_id'      => 1,
                            'type'          => 1,
                            'money'         => 1,
                            'game_coin'     => 1,
                            'order_id'      => 0,
                            'create_time'   => 1,
                            'remark'        => "恭喜您完成任务，获取奖励￥$totalMoney",
                        );
                        $dbReward -> add($rewardDate);
                        //发任务奖励
                        



                        if($newAdd && $newUpdateCount && $resStaffUpdate){
                            echo "<script>alert('提交成功，奖励已发放，已为您刷新任务。');window.location.href='".U('Home/Task/taskOffice')."';</script>";
                        }else{
                            echo "<script>alert('提交失败，请及时截图联系总公司。');window.location.href='".U('Home/Task/taskOffice')."';</script>";
                        }
                    }
                }
            }
        }else{
            $data = array(
                'uid'       => $_SESSION['userid'],
                'get_time'  => date('Y-m-d H:i:s'),
                'done_time' => '',  //不可用null，否则无法插入数据
                'status'    => 1,
                'reward'    => null,
                'get_money' => 0,
                'task_count'=> 0,
            );
            foreach($weeklyTypeOne as $k => $v){
                $data['task_id']    = $weeklyTypeOne[$k]['task_id'];
                $data['inneed']     = $weeklyTypeOne[$k]['inneed'];
                $dbTaskDone -> add_done($data);
            }
            $result = 0;
        }
        return $result;
    }


    /**判断任务完成情况**/
    public function taskJudge(){
        $dbStaff        = D('Staff');
        $dbTaskDone     = D('TaskDone');
        $doneDaily      = $dbTaskDone -> get_all_task($_SESSION['userid'],'','1');
        $left           = $dbStaff    -> get_staff_by_id($_SESSION['userid']);            //获取当前用户的信息
        $date           = array(
            'status'    => 2,
            'done_time' => date('Y-m-d H:i:s'),
        );
        if($doneDaily){
            $dbUserShip     = D('UserShip');
            $dbUserCharge   = D('UserCharge');
            $taskCount      = $dbTaskDone -> get_task_field($_SESSION['userid'],'task_count','field');//完成任务次数
            //5：判断分享推广专员任务（日常任务） $dailyTaskFiveStatus
            $dailyTaskFiveStatus = 0;
            $dailyTaskFiveInneed = 0;
            $dailyTaskFiveId     = 0;
            foreach ($doneDaily as $k5 => $v5){
                if($doneDaily[$k5]['name'] == '分享推广专员' && $doneDaily[$k5]['task_count'] == $taskCount){
                    $dailyTaskFiveInneed    = $doneDaily[$k5]['inneed']*($taskCount+1);
                    $dailyTaskFiveStatus    = $doneDaily[$k5]['status'];
                    $dailyTaskFiveId        = $doneDaily[$k5]['id'];
                }
            }
            if($dailyTaskFiveStatus == 1){ //未完成任务状态
                if($left['recommend_num'] >= $dailyTaskFiveInneed){  //完成任务
                    $dbTaskDone   -> save_done('id', $dailyTaskFiveId, $date);
                }
            }
            //1：判断分享玩家任务（日常任务和额外任务） $dailyTaskOneStatus  $extraTaskOneReward
            $twoGameId  = $left['game_id'];
            $number = $dbUserShip -> get_user_by_recommend($twoGameId, 'count');
            $dailyTaskOneStatus = 0;
            $dailyTaskOneInneed = 0;
            $dailyTaskOneId     = 0;
            foreach ($doneDaily as $k1 => $v1){
                if($doneDaily[$k1]['name'] == '分享玩家' && $doneDaily[$k1]['task_count'] == $taskCount){
                    $dailyTaskOneInneed     = $doneDaily[$k1]['inneed']*($taskCount+1);
                    $dailyTaskOneStatus     = $doneDaily[$k1]['status'];
                    $dailyTaskOneId         = $doneDaily[$k1]['id'];
                }
            }
            if($dailyTaskOneStatus == 1){
                if($number >= $dailyTaskOneInneed){
                    $dbTaskDone -> save_done('id', $dailyTaskOneId, $date);
                }
            }
            //2：判断首充金额任务（日常任务） $dailyTaskTwoStatus
            $users = $dbUserShip ->  get_user_by_recommend($twoGameId,'select');            //所有推荐人是此用户的玩家，时间限制在充值时进行限制
            foreach ($users as $k2 => $v2){
                $game_id[] = $users[$k2]['game_id'];
            }
            $chargeFirstMoney = $dbUserCharge -> get_user_charge($game_id,'1','money');//本周内首充金额
            $dailyTaskTwoStatus = 0;
            $dailyTaskTwoInneed = 0;
            $dailyTaskTwoId     = 0;
            foreach ($doneDaily as $k22 => $v22){
                if($doneDaily[$k22]['name']  == '首充金额' && $doneDaily[$k22]['task_count'] == $taskCount){
                    $dailyTaskTwoInneed     = $doneDaily[$k22]['inneed']*($taskCount+1);
                    $dailyTaskTwoStatus     = $doneDaily[$k22]['status'];
                    $dailyTaskTwoId         = $doneDaily[$k22]['id'];
                }
            }
            if($dailyTaskTwoStatus == 1){
                if($chargeFirstMoney >= $dailyTaskTwoInneed){
                    $dbTaskDone -> save_done('id', $dailyTaskTwoId, $date);
                }
            }
            //4：判断充值业绩任务（日常任务） $dailyTaskFourStatus
            $chargeMoney = $dbUserCharge -> get_user_charge($game_id,'2','money');       //本周内充值金额
            $dailyTaskFourStatus = 0;
            $dailyTaskFourInneed = 0;
            $dailyTaskFourId     = 0;
            foreach ($doneDaily as $k4 => $v4){
                if($doneDaily[$k4]['name']  == '充值业绩' && $doneDaily[$k4]['task_count'] == $taskCount){
                    $dailyTaskFourInneed    = $doneDaily[$k4]['inneed']*($taskCount+1);
                    $dailyTaskFourStatus    = $doneDaily[$k4]['status'];
                    $dailyTaskFourId        = $doneDaily[$k4]['id'];
                }
            }
            if($dailyTaskFourStatus == 1){
                if($chargeMoney >= $dailyTaskFourInneed){
                    $dbTaskDone -> save_done('id', $dailyTaskFourId, $date);
                }
            }
            $status = [];
            foreach($doneDaily as $kAll => $vAll){
                $status[]   = $doneDaily[$kAll]['status'];
            }
            //$status ：日常任务总状态值 => 0：日常任务未全部完成；1：日常任务全部完成；2：未领取日常任务
            if(in_array('1',$status)){
                $statusTotal = 0;
            }else{
                $statusTotal = 1;
            }
        }else{
            $number             = 0;
            $chargeMoney        = 0;
            $statusTotal        = 2;
            $chargeFirstMoney   = 0;
            $dailyTaskFourInneed= 0;
            $dailyTaskFiveInneed= 0;
        }
        $array = array(
            'taskFiveActual'    => $left['recommend_num'],
            'taskFiveInneed'    => $dailyTaskFiveInneed,
            'taskOneActual'     => $number,
            'taskTwoActual'     => $chargeFirstMoney,
            'taskFourActual'    => $chargeMoney,
            'taskFourInneed'    => $dailyTaskFourInneed,
            'statusTotal'       => $statusTotal
        );
        return $array;
    }
}