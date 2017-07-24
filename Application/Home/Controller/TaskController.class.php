<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    /**我的任务**/
    public function index(){
        $dbTaskDone     = D('TaskDone');
        $dbStaff        = D('Staff');
        $dbUserShip     = D('UserShip');
        $dbUserCharge   = D('UserCharge');
        $resDoneOne     = $dbTaskDone -> get_this_week_task($_SESSION['userid'],'','1');    //获取这周用户已领取的日常任务列表
        $resDoneTwo     = $dbTaskDone -> get_this_week_task($_SESSION['userid'],'','2');    //获取这周用户已领取的额外任务列表
        $date           = date('Y-m-d H:i:s');
        $resDoneStart   = $dbTaskDone -> get_start_time($date);
        $resDoneEnd     = $dbTaskDone -> get_end_time($date);
        $resDoneCount   = $dbTaskDone -> get_this_week_all_task($_SESSION['userid'],'');    //获取这周用户已领取的所有任务列表
        $doneNo         = $dbTaskDone -> get_count($resDoneCount,'status',2);               //获取这周用户已领取的已完成的任务的数量
        $doingNo        = $dbTaskDone -> get_count($resDoneCount,'status',1);               //获取这周用户已领取的正在进行的任务的数量
        $noUseNo        = $dbTaskDone -> get_count($resDoneCount,'status',3);               //获取这周用户已领取的未完成的任务的数量
        $left           = $dbStaff    -> get_staff_by_id($_SESSION['userid']);              //已分享推广专员数量
        $fiveCount      = $left['recommend_num'];
        $twoGameId      = $left['game_id'];
        $oneCount       = $dbUserShip -> get_weekly_user_by_recommend($twoGameId, 'count'); //已分享玩家数量
        $users          = $dbUserShip ->  get_user_by_recommend($twoGameId,'select');
        if(empty($users)){
            $twoCount   = 0;
            $threeCount = 0;
        }else{
            foreach ($users as $k2 => $v2) {
                $game_id[] = $users[$k2]['game_id'];
            }
            $twoCount    = $dbUserCharge -> get_user_charge($game_id,'1','money');          //首充金额
            $threeCount  = $dbUserCharge -> get_user_charge($game_id,'2','money');          //充值业绩
        }
        foreach($resDoneOne as $k => $v){
            if($resDoneOne[$k]['name']  == '分享推广专员'){
                $resDoneOne[$k]['count'] = $fiveCount;
            }
            if($resDoneOne[$k]['name']  == '分享玩家'){
                $resDoneOne[$k]['count'] = $oneCount;
            }
            if($resDoneOne[$k]['name']  == '首充金额'){
                $resDoneOne[$k]['count'] = $twoCount;
            }
            if($resDoneOne[$k]['name']  == '充值业绩'){
                $resDoneOne[$k]['count'] = $threeCount;
            }
        }
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
        $dbStaff            = D('Staff');
        $dbTaskDone         = D('TaskDone');
        $dbParameter        = D('Parameter');
        $class              = $dbStaff      -> get_staff_league($_SESSION['userid']);   //获取当前等陆用户的加盟商等级
        if($class == 0){        //推广专员无任务
            $weeklyTypeOne  = null;
            $weeklyTypeTwo  = null;
        }else {
            $weeklyTypeOne  = $dbTaskWeekly -> get_weekly_type('1', $class);    //获取本周日常任务
            $weeklyTypeTwo  = $dbTaskWeekly -> get_weekly_type('2','');         //获取本周额外任务
            //分红奖励
            $parameterBase  = $dbParameter  -> get_parameter_by_id('5');
            $bonusMoney     = task_bonus($_SESSION['userid'],$parameterBase['value']);//个人所得游戏分红金额
            $bonusMoney     = $bonusMoney/0.8;
            //预计任务奖励
            foreach($weeklyTypeOne as $item){
                if($item['name'] == "分享推广专员"){
                    $inneed['rec_num'] = $item['inneed'];
                }
                if($item['name'] == "充值业绩"){
                    $inneed['recharge'] = $item['inneed'];
                }
            }
            $oldData        = D('staff') -> get_staff_by_id($_SESSION['userid']);
            $service_number = $oldData['service_number'];
            $taskMoney      = taskMoney($_SESSION['userid'],$inneed['rec_num'],$inneed['recharge'],$service_number);
            //插入结算数据
            $in_data = array(
                'uid'       => $_SESSION['userid'],
                'status'    => 8,
                'reward'    => $taskMoney,
                'task_id'   => 0,
                'get_time'  => date('Y-m-d H:i:s'),
                'done_time' => '',  //不可用null，否则无法插入数据
            );
            $start          = D('task_done') -> get_start_time(date('Y-m-d H:i:s'));
            $end            = D('task_done') -> get_end_time(date('Y-m-d H:i:s'));
            $select_data = array(
                'uid'       => $_SESSION['userid'],
                'status'    => 8,
                'task_id'   => 0,
                'get_time'  => array(array('egt',$start),array('elt',$end)),
            );
            $is_set         = $dbTaskDone -> where($select_data) ->find();
            if(empty($is_set)){
                $dbTaskDone -> add_done($in_data);
            }
            //分享推广专员时拿到的奖励
            $elseMoney      = 200/0.8;
            //收益总金额
            $moneyOne       = $taskMoney + $bonusMoney + $elseMoney;
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
                        'get_money' => 1,
                    );
                    $weeklyTypeOne = $dbTaskWeekly -> get_weekly_type('1',$class);
                    foreach($weeklyTypeOne as $k => $v){
                        $data['task_id']    = $weeklyTypeOne[$k]['task_id'];
                        $data['inneed']     = $weeklyTypeOne[$k]['inneed'];
                        $resDone = $dbTaskDone -> add_done($data);
                    }
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


    /**任务手动结算**/
    public function taskWhat(){
        $staff          = D('Staff');
        $taskDone       = D('TaskDone');
        $userShip       = D('UserShip');
        $userCharge     = D('UserCharge');
        $dbParameter    = D('Parameter');
        $dbStaffInfo    = D('StaffInfo');
        /*
        $dbTaskWeekly   = D('TaskWeekly');
        $dbGameCount    = D('GameCount');
        $class          = $staff    -> get_staff_league($_SESSION['userid']);           //获取当前等陆用户的加盟商等级
        $doneExtra      = $taskDone -> get_this_week_task($_SESSION['userid'],'','2');  //额外任务
        $refereeCount   = $staff    -> count_staff_by_referee($_SESSION['userid']);   //获取分享推广专员总人数
        */
        $doneDaily      = $taskDone -> get_this_week_task($_SESSION['userid'],'','1');  //日常任务
        $left           = $staff    -> get_staff_by_id($_SESSION['userid']);            //获取当前用户的信息
        $dateTime       = date('Y-m-d H:i:s');
        $date           = array(
            'status'    => 2,
            'done_time' => date('Y-m-d 05:20:00'),
        );
        if(empty($doneDaily)){
        }else {
            //5：判断分享推广专员任务（日常任务） $dailyTaskFiveStatus
            foreach ($doneDaily as $k5 => $v5) {
                if ($doneDaily[$k5]['name']  == '分享推广专员') {
                    $dailyTaskFiveInneed    = $doneDaily[$k5]['inneed'];
                    $dailyTaskFiveStatus    = $doneDaily[$k5]['status'];
                    $dailyTaskFiveId        = $doneDaily[$k5]['id'];
                }
            }
            if($dailyTaskFiveStatus == 1){ //未完成任务状态
                if($left['recommend_num'] >= $dailyTaskFiveInneed){  //完成任务
                    $taskDone   -> save_done('id', $dailyTaskFiveId, $date);
                }
            }
            //1：判断分享玩家任务（日常任务和额外任务） $dailyTaskOneStatus  $extraTaskOneReward
            $twoGameId  = $left['game_id'];
            $number = $userShip -> get_weekly_user_by_recommend($twoGameId, 'count');
            foreach ($doneDaily as $k1 => $v1) {
                if ($doneDaily[$k1]['name']  == '分享玩家') {
                    $dailyTaskOneInneed     = $doneDaily[$k1]['inneed'];
                    $dailyTaskOneStatus     = $doneDaily[$k1]['status'];
                    $dailyTaskOneId         = $doneDaily[$k1]['id'];
                }
            }
            if ($dailyTaskOneStatus == 1) {
                if ($number >= $dailyTaskOneInneed) {
                    $taskDone -> save_done('id', $dailyTaskOneId, $date);
                }
            }
            //2：判断首充金额任务（日常任务） $dailyTaskTwoStatus
            $users = $userShip ->  get_user_by_recommend($twoGameId,'select');      //所有推荐人是此用户的玩家，时间限制在充值时进行限制
            foreach ($users as $k2 => $v2) {
                $game_id[] = $users[$k2]['game_id'];
            }
            $chargeFirstNumber = $userCharge -> get_user_charge($game_id,'1','money');      //本周内首充金额
            foreach ($doneDaily as $k22 => $v22) {
                if ($doneDaily[$k22]['name']  == '首充金额') {
                    $dailyTaskTwoInneed     = $doneDaily[$k22]['inneed'];
                    $dailyTaskTwoStatus     = $doneDaily[$k22]['status'];
                    $dailyTaskTwoId         = $doneDaily[$k22]['id'];
                }
            }
            if ($dailyTaskTwoStatus == 1) {
                if ($chargeFirstNumber >= $dailyTaskTwoInneed) {
                    $taskDone -> save_done('id', $dailyTaskTwoId, $date);
                }
            }
            //4：判断充值业绩任务（日常任务） $dailyTaskFourStatus
            $chargeMoney = $userCharge -> get_user_charge($game_id,'2','money');        //本周内充值金额
            foreach ($doneDaily as $k4 => $v4) {
                if ($doneDaily[$k4]['name']  == '充值业绩') {
                    $dailyTaskFourInneed    = $doneDaily[$k4]['inneed'];
                    $dailyTaskFourStatus    = $doneDaily[$k4]['status'];
                    $dailyTaskFourId        = $doneDaily[$k4]['id'];
                }
            }
            if ($dailyTaskFourStatus == 1) {
                if ($chargeMoney >= $dailyTaskFourInneed) {
                    $taskDone -> save_done('id', $dailyTaskFourId, $date);
                }
            }
            /*
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
            */
            foreach($doneDaily as $kAll => $vAll){
                $status[]   = $doneDaily[$kAll]['status'];
            }
            //$status ：日常任务总状态值 => 1：日常任务全部完成；0：日常任务未全部完成
            if(in_array('1',$status)){
                $statusTotal = 0;
            }else{
                $statusTotal = 1;
            }
            //$dailytaskReward ： 日常任务总金额
            if($statusTotal == 1 && !empty($doneDaily)) {
                foreach($doneDaily as $key => $val){
                    $getMoney[] = $doneDaily[$key]['get_money'];
                }
                if(in_array(2,$getMoney)){
                    echo "<script>alert('奖励已发放,请到奖励中心查看。');window.location.href='".U('Home/Task/index')."';</script>";
                }else{
                    $start          = $taskDone -> get_start_time($dateTime);
                    $end            = $taskDone -> get_end_time($dateTime);
                    $taskMoney      = $taskDone->where(array('uid'=>$_SESSION['userid'],'status'=>8,'task_id'=>0,'get_time'=>array(array('gt',$start),array('lt',$end))))->getField('reward');
                    $oldData        = D('staff') -> get_staff_by_id($_SESSION['userid']);
                    //增加信用分
                    $infoCredit     = $dbStaffInfo -> get_staff_by_uid($_SESSION['userid']);
                    $infoCreditNum  = $infoCredit['credit_num'] + 1;
                    $infoCreditNum  = $infoCreditNum >= 4 ? 4 : $infoCreditNum;
                    $infoCreditValue= $dbStaffInfo -> get_credit($infoCreditNum);
                    $infoCred       = array(
                        'credit_value'  => $infoCreditValue,
                        'credit_num'    => $infoCreditNum,
                    );
                    $dbStaffInfo    -> save_staff_by_uid($_SESSION['userid'],$infoCred);
                    //分红奖励
                    $parameterBase  = $dbParameter -> get_parameter_by_id('5');
                    $parameter      = $dbParameter -> get_parameter_by_id('3');
                    $bonusMoney     = bonus_personal($_SESSION['userid'],'1',$parameterBase['value']);
                    $bonusMoney     = $bonusMoney/0.8;
                    //任务总金额
                    $totalMoney     = $bonusMoney + $taskMoney;
                    $discount       = $parameter['value']/100;
                    //更新状态为已发放奖励状态
                    $doneData       = array(
                        'get_money' => 2,
                        'done_time' => date('Y-m-d 05:20:00'),
                    );
                    $doneMap = array(
                        'uid'       => $_SESSION['userid'],
                        'get_time'  => array(array('gt',$start),array('lt',$end)),
                    );
                    $taskDone -> where($doneMap) -> save($doneData);
                    //修改recommend_num值=总值-指标，保留此字段值，用于下次任务
                    $staffInfo      = $staff -> get_staff_by_id($_SESSION['userid']);
                    $num            = $staffInfo['recommend_num'];
                    $newNum         = $num - $dailyTaskFiveInneed;
                    $newsNum        = $newNum <= 0 ? 0 : $newNum;
                    $newsNums['recommend_num'] = $newsNum;      //TODO: 测试时，为防止数据被减为负值。
                    $staff -> save_staff_by_id($_SESSION['userid'], $newsNums);
                    //staff 数据
                    $staffArr[] = array(
                        'id'    => $_SESSION['userid'],
                        'data'  => array(
                            'consume_coin'  => $oldData['consume_coin'] + ($totalMoney * $discount),
                            'money'         => $oldData['money'] + $totalMoney * (1 - $discount),
                            'income'        => $oldData['income'] + $totalMoney,
                        )
                    );
                    //流水表 flow
                    $flowArr[]  = array(
                        'id'    => $_SESSION['userid'],
                        'data'  => array(
                            'uid'           => $_SESSION['userid'],
                            'type'          => 1,
                            'money'         => $totalMoney,
                            'order_id'      => make_orderId(),
                            'create_time'   => date('Y-m-d H:i:s'),
                        )
                    );
                    //奖励表 reward  日常任务奖励
                    $rewardDailyArr[]   = array(
                        'id'    => $_SESSION['userid'],
                        'data'  => array(
                            'uid'           => $_SESSION['userid'],
                            'type'          => 1,       //日常任务奖励
                            'money'         => $totalMoney * (1 - $discount),
                            'game_coin'     => $totalMoney * $discount,
                            'order_id'      => make_orderId(),
                            'create_time'   => date('Y-m-d H:i:s'),
                            'remarks'       => "用户手动提交，完成本周日常任务，奖励总金额 $totalMoney 元",
                        )
                    );
                    //通知表 notice
                    $noticeArr[]  = array(
                        'id'    => $_SESSION['userid'],
                        'data'  =>array(
                            'uid'           => $_SESSION['userid'],
                            'kind'          => '2',
                            'poster'        => 'system',
                            'notice_type_id'=> 3,
                            'notice_title'  => '恭喜您已完成本周任务',
                            'notice_content'=> "获得本周任务总金额 $totalMoney 元",
                        )

                    );
                    $noticeArr[]  = array(
                        'data'  => array(
                            'uid'               => $_SESSION['userid'],
                            'kind'              => '2',
                            'poster'            => 'system',
                            'notice_type_id'    => 4,
                            'notice_title'      => '恭喜您已完成本周任务',
                            'notice_content'    => "获得本周任务总金额 $totalMoney 元",
                        )
                    );
                    payReward($staffArr,$rewardDailyArr,$flowArr,$noticeArr);
                    echo "<script>alert('恭喜您完成本周任务，奖励已发放，请到奖励中心查看详情。');window.location.href='".U('Home/Task/index')."';</script>";
                }
            }else{
                echo "<script>alert('任务还未全部完成哦，加油吧。');window.location.href='".U('Home/Task/index')."';</script>";
            }
            /* TODO: 额外任务模块暂未开启，现注释掉此部分，需要时解除注释即可。
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
            */
        }
    }
}
