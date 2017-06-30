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


    /**判断分享推广专员任务是否完成**/
    public function shareTg(){
        $staff      = D('Staff');
        $taskDone   = D('TaskDone');
        $id         = $_SESSION['userid'];
        $res  = $staff      -> count_staff_by_referee($id);
        $done = $taskDone   -> get_this_week_task('1');
        $left = $staff      -> get_staff_by_id($id);
        foreach($done as $k => $v){
            if($done[$k]['name'] == '分享推广专员'){ //任务名称必须设置成 分享推广专员
                $inneed = $done[$k]['inneed'];
            }
        }
        $data['recommend_num'] =  $res;
        if($left['recommend_num'] == 0){ //第一次做任务
            $data['recommend_left_num'] =  $res - $inneed;
        }else{
            $data['recommend_left_num'] = $res - $left['recommend_num'] - $inneed + $left['recommend_left_num'];
        }
        $data['recommend_left_num'] = $data['recommend_left_num'] <= 0 ? 0 :$data['recommend_left_num'];
        $resStaff = $staff -> save_staff_by_id($id,$data);
        if($resStaff){
            //数据写入成功 接下来 发奖励、写流水
        }else{
            $this -> error('数据写入失败');
        }
    }


    /**判断分享了几个玩家**/     //日常任务和额外任务要分开判断
    public function shareUser(){
        $userShip   = D('UserShip');
        $taskDone   = D('TaskDone');
        $number     = $userShip -> get_user_by_superior();
        $doneDaily  = $taskDone   -> get_this_week_task('1');  //日常任务
        $doneExtra  = $taskDone   -> get_this_week_task('2');  //额外任务
        foreach($doneDaily as $k => $v){
            if($doneDaily[$k]['name'] == '分享玩家'){ //任务名称必须设置成 分享推广专员
                $inneed = $doneDaily[$k]['inneed'];
            }
        }
        foreach($doneExtra as $k => $v){
            $where['name'] = '分享玩家，额外任务';

            //根据任务名称获取任务的的状态

        }
        if($number < $inneed){
            //任务未完成
        }else{
            //任务完成 发奖励、写流水
//            code...
            //判断额外任务
            $extraNumber = $inneed - $number;
//            code...
        }


    }


    /**判断首次充值**/
    public function firstCharge(){
        //字段：firstCharge (decimal)
    }


    /**判断是否完成三次游戏任务**/
    public function gameTask(){

    }


    /**判断充值业绩**/       //日常任务充值和额外充值分开判断
    public function chargeInneed(){

    }









}
