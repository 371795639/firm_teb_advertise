<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    /**我的任务**/
    public function index(){
        $dbTaskDone     = D('TaskDone');
        $resDoneOne     = $dbTaskDone -> get_this_week_task($_SESSION['userid'],'','1');
        $resDoneTwo     = $dbTaskDone -> get_this_week_task($_SESSION['userid'],'','2');
        $date           = date('Y-m-d H:i:s');
        $resDoneStart   = $dbTaskDone -> get_start_time($date);
        $resDoneEnd     = $dbTaskDone -> get_end_time($date);
        $resDoneCount   = $dbTaskDone -> get_this_week_all_task($_SESSION['userid'],'');
        $doneNo         = $dbTaskDone -> get_count($resDoneCount,'status',2);
        $doingNo        = $dbTaskDone -> get_count($resDoneCount,'status',1);
        $noUseNo        = $dbTaskDone -> get_count($resDoneCount,'status',3);
        $this -> assign('doneNo',$doneNo);
        $this -> assign('doingNo',$doingNo);
        $this -> assign('noUseNo',$noUseNo);
        $this -> assign('resDoneEnd',$resDoneEnd);
        $this -> assign('resDoneOne',$resDoneOne);
        $this -> assign('resDoneTwo',$resDoneTwo);
        $this -> assign('resDoneStart',$resDoneStart);
        $this -> display('Task/index');
    }


    /**任务大厅**/
    public function taskOffice(){
        $dbTaskWeekly       = D('TaskWeekly');
        $dbTaskDone         = D('TaskDone');
        $class              = D('Staff')    -> get_staff_league($_SESSION['userid']);   //获取当前等陆用户的加盟商等级
        if($class == 0){        //推广专员无任务
            $weeklyTypeOne  = null;
            $weeklyTypeTwo  = null;
        }else {
            $weeklyTypeOne  = $dbTaskWeekly -> get_weekly_type('1', $class);    //获取本周日常任务
            $weeklyTypeTwo  = $dbTaskWeekly -> get_weekly_type('2','');         //获取本周额外任务
            $moneyOne       = $dbTaskWeekly -> get_weekly_money('1',$class);    //获取本周日常任务总金额
            $moneyTwo       = $dbTaskWeekly -> get_weekly_money('2','');        //获取本周额外任务总金额
        }
        $taskDaily          = $dbTaskDone   -> get_this_week_task($_SESSION['userid'],'','1');  //获取用户已领取的日常任务列表
        foreach($weeklyTypeOne as $k => $v){
            $task_id = $weeklyTypeOne[$k]['task_id'];
            $resDone = $dbTaskDone -> get_done_by_uid('task_id',$task_id,'find');
            $weeklyTypeOne[$k]['nstatus'] = $resDone['status'];
        }
        foreach($weeklyTypeTwo as $k => $v){
            $task_id = $weeklyTypeTwo[$k]['task_id'];
            $resDone = $dbTaskDone -> get_done_by_uid('task_id',$task_id,'find');
            $weeklyTypeTwo[$k]['nstatus'] = $resDone['status'];
        }
        if($taskDaily){
            $daily = 1 ; //已领取
            foreach($taskDaily as $k => $v){
                $status[] = $taskDaily[$k]['status'];
            }
        }else{
            $daily = 2 ; //未领取
        }
        if(empty($taskDaily) || in_array('1',$status)){
            $extra = 1; //不可领取;
        }else{
            $extra = 2; //可领取
        }
        $extra = 1; //TODO：现在设置额外任务暂未开放，等开放时删除此行代码即可。
        $this -> assign('daily',$daily);
        $this -> assign('extra',$extra);
        $this -> assign('class',$class);
        $this -> assign('moneyOne',$moneyOne);
        $this -> assign('moneyTwo',$moneyTwo);
        $this -> assign('weeklyTypeOne',$weeklyTypeOne);
        $this -> assign('weeklyTypeTwo',$weeklyTypeTwo);
        $this -> display('Task/taskOffice');
    }


    /**领取任务**/
    public function taskOfficeDetail($method=null){
        $dbTaskDone     = D('TaskDone');
        $dbTaskWeekly   = D('TaskWeekly');
        $taskSet        = $dbTaskDone -> get_this_week_task($_SESSION['userid'],'','1');
        $class          = D('Staff')  -> get_staff_league($_SESSION['userid']);     //获取当前等陆用户的加盟商等级
        switch($method){
            case 'daily':
                if($taskSet){
                    //日常任务在表中已经写入，这次点击只是查看
                }else{
                    $data = array(
                        'uid'       => $_SESSION['userid'],
                        'get_time'  => date('Y-m-d H:i:s'),
                        'done_time' => '',  //不可用null，否则无法插入数据
                        'status'    => 1,
                        'reward'    => null,
                    );
                    $weeklyTypeOne = $dbTaskWeekly -> get_weekly_type('1',$class);
                    foreach($weeklyTypeOne as $k => $v){
                        $data['task_id']    = $weeklyTypeOne[$k]['task_id'];
                        $data['inneed']     = $weeklyTypeOne[$k]['inneed'];
                        $resDone = $dbTaskDone -> add_done($data);
                    }
                    //插入结算数据
                    $date = array(
                        'uid'       => $_SESSION['userid'],
                        'status'    => 8,
                        'reward'    => '0,0,0',
                        'task_id'   => 0,
                        'get_time'  => date('Y-m-d H:i:s'),
                        'done_time' => '',  //不可用null，否则无法插入数据
                    );
                    $dbTaskDone -> add_done($date);
                }
                break;
            case 'extra':
                foreach($taskSet as $k => $v){
                    $status[] = $taskSet[$k]['status'];
                }
                if(in_array('1',$status)){
                    //日常任务的状态值有1，说明日常任务没有都完成，不能领取额外任务
                }else {
                    $weeklyDone = $dbTaskDone -> get_this_week_all_task($_SESSION['userid'],'');
                    $resDone    = $dbTaskDone -> i_array_column($weeklyDone, 'task_id');
                    $task_id    = I('task_id');
                    if (in_array($task_id, $resDone)) {
                        //此额外任务在表中已经写入，这次点击只是查看
                    }else{
                        $resStaff = D('Task') -> get_task_by_id($task_id);
                        $data = array(
                            'task_id'   => $task_id,
                            'uid'       => $_SESSION['userid'],
                            'inneed'    => $resStaff['inneed'],
                            'get_time'  => date('Y-m-d H:i:s'),
                            'done_time' => '',  //不可用null，否则无法插入数据
                            'status'    => 1,
                        );
                        $resDone  = $dbTaskDone->add_done($data);
                    }
                }
                break;
        }
        if($resDone){
            $this -> redirect('Task/taskOffice');
        }else{
            $this -> redirect('Task/taskOffice');
        }
    }


    /**
     * 说明
     * 日常任务判断：$dailyTaskOneStatus、$dailyTaskTwoStatus、$dailyTaskThreeStatus、$dailyTaskFourStatus、$dailyTaskFiveStatus的值，为2完成任务，为1未完成任务
     * 额外任务：如果$status中不含有1且$doneExtra不为空，执行额外任务逻辑代码，否则说明日常任务未全部完成或者全部完成日常任务但是未领取额外任务。
     * 额外任务判断：$extraTaskOne、$extraTaskTwo值存在，则说明已领取，$extraTaskOneReward、$extraTaskTwoReward是对应额外任务的额外奖励
     */
    public function taskWhat(){
        $staff          = D('Staff');
        $taskDone       = D('TaskDone');
        $dbTaskWeekly   = D('TaskWeekly');
        $userShip       = D('UserShip');
        $userCharge     = D('UserCharge');
        $dbGameCount    = D('GameCount');
        $class          = $staff    -> get_staff_league($_SESSION['userid']);           //获取当前等陆用户的加盟商等级
        $doneDaily      = $taskDone -> get_this_week_task($_SESSION['userid'],'','1');  //日常任务
        $doneExtra      = $taskDone -> get_this_week_task($_SESSION['userid'],'','2');  //额外任务
//        $refereeCount   = $staff    -> count_staff_by_referee($_SESSION['userid']);   //获取分享推广专员总人数
        $left           = $staff    -> get_staff_by_id($_SESSION['userid']);            //获取当前用户的信息
        $date           = array(
            'status'    => 2,
            'done_time' => date('Y-m-d 5:20:00'),
        );
        if(empty($doneDaily)){
            $dailytaskReward = 0;
        }else {
            //5：判断分享推广专员任务（日常任务） $dailyTaskFiveStatus
            foreach ($doneDaily as $k => $v) {
                if ($doneDaily[$k]['name']  == '分享推广专员') {
                    $dailyTaskFiveInneed    = $doneDaily[$k]['inneed'];
                    $dailyTaskFiveStatus    = $doneDaily[$k]['status'];
                    $dailyTaskFiveId        = $doneDaily[$k]['id'];
                }
            }
            if($dailyTaskFiveStatus == 1){ //未完成任务状态
                if($left['recommend_num'] >= $dailyTaskFiveInneed){  //完成任务
                    $taskDone   -> save_done('id', $dailyTaskFiveId, $date);
                }
            }
            //1：判断分享玩家任务（日常任务和额外任务） $dailyTaskOneStatus  $extraTaskOneReward
            $number = $userShip -> get_weekly_user_by_superior($_SESSION['userid'], 'count');
            foreach ($doneDaily as $k => $v) {
                if ($doneDaily[$k]['name']  == '分享玩家') {
                    $dailyTaskOneInneed     = $doneDaily[$k]['inneed'];
                    $dailyTaskOneStatus     = $doneDaily[$k]['status'];
                    $dailyTaskOneId         = $doneDaily[$k]['id'];
                }
            }
            if ($dailyTaskOneStatus == 1) {
                if ($number > $dailyTaskOneInneed) {
                    $taskDone -> save_done('id', $dailyTaskOneId, $date);
                }
            }
            //2：判断首充人数任务（日常任务） $dailyTaskTwoStatus
            $users = $userShip ->  get_user_by_superior($_SESSION['userid'],'select');      //所有推荐人是此用户的玩家，时间限制在充值时进行限制
            foreach ($users as $k => $v) {
                $game_id[] = $users[$k]['game_id'];
            }
            $chargeFirstNumber = $userCharge -> get_user_charge($game_id,'1','count');      //本周内首充人数
            foreach ($doneDaily as $k => $v) {
                if ($doneDaily[$k]['name']  == '首充人数') {
                    $dailyTaskTwoInneed     = $doneDaily[$k]['inneed'];
                    $dailyTaskTwoStatus     = $doneDaily[$k]['status'];
                    $dailyTaskTwoId         = $doneDaily[$k]['id'];
                }
            }
            if ($dailyTaskTwoStatus == 1) {
                if ($chargeFirstNumber > $dailyTaskTwoInneed) {
                    $taskDone -> save_done('id', $dailyTaskTwoId, $date);
                }
            }
            //4：判断充值业绩任务（日常任务） $dailyTaskFourStatus
            $chargeMoney = $userCharge -> get_user_charge($game_id,'2','money');        //本周内充值金额
            foreach ($doneDaily as $k => $v) {
                if ($doneDaily[$k]['name']  == '充值业绩') {
                    $dailyTaskFourInneed    = $doneDaily[$k]['inneed'];
                    $dailyTaskFourStatus    = $doneDaily[$k]['status'];
                    $dailyTaskFourId        = $doneDaily[$k]['id'];
                }
            }
            if ($dailyTaskFourStatus == 1) {
                if ($chargeMoney > $dailyTaskFourInneed) {
                    $taskDone -> save_done('id', $dailyTaskFourId, $date);
                }
            }
            //3：判断完成3次游戏任务（日常任务）  $dailyTaskThreeStatus
            $GameCount  = $dbGameCount -> get_game($_SESSION['userid'],'find');
            $gameCounts = $GameCount['playCount'];
            foreach ($doneDaily as $k => $v) {
                if ($doneDaily[$k]['name']  == '游戏任务') {
                    $dailyTaskThreeInneed   = $doneDaily[$k]['inneed'];
                    $dailyTaskThreeStatus   = $doneDaily[$k]['status'];
                    $dailyTaskThreeId       = $doneDaily[$k]['id'];
                }
            }
            if ($dailyTaskThreeStatus == 1) {
                if ($gameCounts > $dailyTaskThreeInneed) {
                    $taskDone -> save_done('id', $dailyTaskThreeId, $date);
                }
            }

            //额外任务模块
            $status = array(
                '0' => $dailyTaskOneStatus,
                '1' => $dailyTaskTwoStatus,
                '2' => $dailyTaskThreeStatus,
                '3' => $dailyTaskFourStatus,
                '4' => $dailyTaskFiveStatus,
            );
            //$statusTotal ：日常任务总状态值 => 1：日常任务全部完成；0：日常任务未全部完成
            if(in_array('1',$status)){
                $statusTotal = 0;
            }else{
                $statusTotal = 1;
            }
            //$dailytaskReward ： 日常任务总金额
            if($statusTotal == 1 && !empty($doneDaily)) {
                $dailytaskReward = $dbTaskWeekly -> get_weekly_money('1', $class);
            }else{
                $dailytaskReward = 0;
            }
            if($statusTotal == 1 && !empty($doneExtra)){
                foreach ($doneExtra as $kk => $vv) {
                    $extraNames[]       = $doneExtra[$kk]['name'];
                }
                //6：额外分享玩家任务
                $extraTaskOneName       = '额外分享玩家';
                $extraTaskOne           = in_array($extraTaskOneName, $extraNames);
                if ($extraTaskOne) { //领取了额外分享玩家任务
                    $extraNumber        = $number - $dailyTaskOneInneed;  //实际额外分享玩家数
                    if($extraNumber < 0){
                        $extraTaskOneReward = 0;
                    }else {
                        $extraNumberFact = $extraNumber >= 50 ? 50 : $extraNumber;  //实际奖励额外分享玩家数
                        $extraTaskOneReward = $extraNumberFact * 2;
                    }
                }
                //7：额外充值业绩任务
                $extraTaskTwoName       = '额外充值业绩';
                $extraTaskTwo           = in_array($extraTaskTwoName, $extraNames);
                if($extraTaskTwo){
                    $extraMoney         = $chargeMoney - $dailyTaskFourInneed;
                    if($extraMoney < 0){
                        $extraTaskTwoReward = 0;
                    }else {
                        switch ($extraMoney) {
                            case $extraMoney <= 500:
                                $extraTaskTwoReward = 100;
                                break;
                            case $extraMoney > 500 && $extraMoney <= 1000:
                                $extraTaskTwoReward = 200;
                                break;
                            case $extraMoney > 1000 && $extraMoney <= 3000:
                                $extraTaskTwoReward = 300;
                                break;
                            case $extraMoney > 3000 && $extraMoney <= 5000:
                                $extraTaskTwoReward = 400;
                                break;
                            case $extraMoney > 5000 && $extraMoney <= 10000:
                                $extraTaskTwoReward = 500;
                                break;
                            case $extraMoney > 10000 :
                                $extraTaskTwoReward = 600;
                                break;
                            default;
                                $extraTaskTwoReward = '...';
                        }
                    }
//                    $extarRewardTotal = $extraTaskOneReward + $extraTaskTwoReward;
                }
            }else{
                $extraTaskOneReward = 0;
                $extraTaskTwoReward = 0;
            }
        }//日常任务的状态修改和额外任务的统计结束
        /**将日常任务总金额、额外任务一金额、额外任务二金额写入taskDone表中**/
        $dateTime   = date('Y-m-d H:i:s');
        $start      = $taskDone -> get_start_time($dateTime);
        $end        = $taskDone -> get_end_time($dateTime);
        $where = array(
            'uid'       => $_SESSION['userid'],
            'task_id'   => 0,
            'get_time'  => array(array('gt',$start),array('lt',$end)),
        );
        $dataDone['reward'] = $dailytaskReward.','.$extraTaskOneReward.",".$extraTaskTwoReward;
        $taskDone -> where($where) -> save($dataDone);
//        $totalReward = $dailytaskReward + $extarRewardTotal;
//        return $totalReward;
    }
}
