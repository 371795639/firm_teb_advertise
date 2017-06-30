<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    /**我的任务**/
    public function index(){
        $dbTaskDone     = D('TaskDone');
        $resDoneOne     = $dbTaskDone -> get_this_week_task('1');
        $resDoneTwo     = $dbTaskDone -> get_this_week_task('2');
        $date           = date('Y-m-d H:i:s');
        $resDoneStart   = $dbTaskDone -> get_start_time($date);
        $resDoneEnd     = $dbTaskDone -> get_end_time($date);
        $resDoneCount   = $dbTaskDone -> get_this_week_all_task();
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
        $this -> display();
    }


    /**任务大厅**/
    public function taskOffice(){
        $dbTaskWeekly   = D('TaskWeekly');
        $dbTaskDone     = D('TaskDone');
        $class          = D('Staff')    -> get_staff_league($_SESSION['userid']);//获取当前等陆用户的加盟商等级
        if($class == 0){//不是加盟商
            $weeklyTypeOne  = null;
            $weeklyTypeTwo  = null;
        }else {
            $weeklyTypeOne  = $dbTaskWeekly -> get_weekly_type('1', $class);//获取本周日常任务
            $weeklyTypeTwo  = $dbTaskWeekly -> get_weekly_type('2');//获取本周额外任务
            $moneyOne       = $dbTaskWeekly -> get_weekly_money('1');//获取本周日常任务总金额
            $moneyTwo       = $dbTaskWeekly -> get_weekly_money('2');//获取本周额外任务总金额
        }
        $taskDaily          = $dbTaskDone   -> get_this_week_task('1');//获取用户已领取的日常任务列表
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
        }else{
            $daily = 2 ; //未领取
        }
        foreach($taskDaily as $k => $v){
            $status[] = $taskDaily[$k]['status'];
        }
        if(empty($taskDaily) || in_array('1',$status)){
            $extra = 1; //不可领取;
        }else{
            $extra = 2; //可领取
        }
        $this -> assign('daily',$daily) ;
        $this -> assign('extra',$extra) ;
        $this -> assign('class',$class) ;
        $this -> assign('moneyOne',$moneyOne);
        $this -> assign('moneyTwo',$moneyTwo);
        $this -> assign('weeklyTypeOne',$weeklyTypeOne);
        $this -> assign('weeklyTypeTwo',$weeklyTypeTwo);
        $this -> display();
    }


    /**领取任务**/
    public function taskOfficeDetail($method=null){
        $dbTaskDone     = D('TaskDone');
        $dbTaskWeekly   = D('TaskWeekly');
        $taskSet        = $dbTaskDone -> get_this_week_task('1');
        $class          = D('Staff')  -> get_staff_league($_SESSION['userid']);//获取当前等陆用户的加盟商等级
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
                    $weeklyDone = $dbTaskDone -> get_done_by_task_id();
                    $resDone    = $dbTaskDone -> i_array_column($weeklyDone, 'task_id');
                    $task_id    = I('task_id');
                    if (in_array($task_id, $resDone)) {
                        //此额外任务在表中已经写入，这次点击只是查看
                    }else{
                        $resStaff = D('Task')->get_task_by_id($task_id);
                        $data = array(
                            'task_id'   => $task_id,
                            'uid'       => $_SESSION['userid'],
                            'inneed'    => $resStaff['inneed'],
                            'get_time'  => date('Y-m-d H:i:s'),
                            'done_time' => '',  //不可用null，否则无法插入数据
                            'status'    => 1,
                        );
                        $resDone = $dbTaskDone->add_done($data);
                    }
                }
                break;
        }
        if($resDone){
            $this -> success('领取成功',U('Home/User/index'));  //TODO:跳转需要重新封装
        }else{
            $this -> error('领取失败');
        }
    }


    /**每天凌晨跑计时器,判断日常任务和额外任务是否完成**/
    public function taskWhat(){
//判断分享推广专员任务（日常任务） $dailyTaskFive
        $staff          = D('Staff');
        $taskDone       = D('TaskDone');
        $userShip       = D('UserShip');
        $userCharge     = D('UserCharge');

        $refereeCount   = $staff    -> count_staff_by_referee($_SESSION['userid']);
        $class          = $staff    -> get_staff_league($_SESSION['userid']);//获取当前等陆用户的加盟商等级
        $doneDaily      = $taskDone -> get_this_week_task('1',$class);  //日常任务
        $doneExtra      = $taskDone -> get_this_week_task('2');  //额外任务
        $left           = $staff    -> get_staff_by_id($_SESSION['userid']);
        foreach($doneDaily as $k => $v){
            if($doneDaily[$k]['name'] == '分享推广专员'){ //任务名称必须设置成 分享推广专员
                $dailyTaskFiveInneed = $doneDaily[$k]['inneed'];
            }
        }
        $data['recommend_num'] = $refereeCount;
        if($left['recommend_num'] == 0){ //第一次做任务
            $data['recommend_left_num'] = $refereeCount - $dailyTaskFiveInneed;
        }else{
            $data['recommend_left_num'] = $refereeCount - $left['recommend_num'] - $dailyTaskFiveInneed + $left['recommend_left_num'];
        }
        $data['recommend_left_num'] = $data['recommend_left_num'] <= 0 ? 0 :$data['recommend_left_num'];
//        $resStaff = $staff -> save_staff_by_id($_SESSION['userid'],$data);
        $result = $staff -> get_staff_by_id($_SESSION['userid']);
        if($result['recommend_num'] > 0 && $result['recommend_left_num'] >= 0){
            $dailyTaskFive = 1;
            //更改 task_done中 分享推广专员 任务的状态为2
        }else{
            $dailyTaskFive = 0;
        }
// return $dailyTaskFive     1：完成日常 分享推广专员 任务；0：未完成日常 分享推广专员 任务


//判断分享玩家任务（日常任务和额外任务） $dailyTaskOne  $extraTaskOneReward
        $number     = $userShip -> count_user_by_superior($_SESSION['userid'],'0');
        foreach($doneDaily as $k => $v){
            if($doneDaily[$k]['name'] == '分享玩家'){ //任务名称必须设置成 分享玩家
                $dailyTaskOneInneed = $doneDaily[$k]['inneed'];
            }
        }
        if($number < $dailyTaskOneInneed){
            $dailyTaskOne = 0;
        }else{
            $dailyTaskOne = 1;
            //更改 task_done中 分享玩家 任务的状态为2
            if(empty($doneExtra)){
                //未领取 额外分享玩家 任务，不发奖励
            }else {
                $extraNumber = $dailyTaskOneInneed - $number;  //实际额外分享玩家数
                $extraNumberFact = $extraNumber >= 50 ? 50 : $extraNumber;  //实际奖励额外分享玩家数
                $extraTaskOneReward = $extraNumberFact * 2;
            }
        }
//return $dailyTaskOne;         //1：完成日常 分享玩家 任务；0：未完成日常 分享玩家 任务
//return $extraTaskOneReward;   //额外分享玩家 任务可获得的奖励


//判断首充人数任务（日常任务） $dailyTaskTwo
        $users = $userShip -> get_user_by_superior($_SESSION['userid']);
        foreach($users as $k => $v){
            $game_id[] = $users[$k]['game_id'];
        }
        $chargeNumber = $userCharge -> get_user_first_charge($game_id,0);   //本周内首充人数
        foreach($doneDaily as $k => $v){
            if($doneDaily[$k]['name'] == '首充人数'){ //任务名称必须设置成 首充人数
                $dailyTaskTwoInneed = $doneDaily[$k]['inneed'];
            }
        }
        if($chargeNumber < $dailyTaskTwoInneed){
            $dailyTaskTwo = 0;
        }else{
            $dailyTaskTwo = 1;
            //更改 task_done中 首充人数 任务的状态为2
        }
//return $dailyTaskTwo;   //1：完成日常 首充人数 任务；2：未完成日常 首充人数 任务


//判断完成3次游戏任务（日常任务）  $dailyTaskThree
        //等待游戏那边的接口 传值 inneed









    }
}
