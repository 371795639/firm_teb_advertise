<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    public function index(){

    }


    /**加载任务大厅页面**/
    public function taskOffice(){
        $dbTaskWeekly = D('TaskWeekly');
        $dbTaskDone = D('TaskDone');
        $weeklyTypeOne  = $dbTaskWeekly -> get_weekly_type('1');
        $weeklyTypeTwo  = $dbTaskWeekly -> get_weekly_type('2');
        $moneyOne       = $dbTaskWeekly -> get_weekly_money('1');
        $moneyTwo       = $dbTaskWeekly -> get_weekly_money('2');
        $taskDaily      = $dbTaskDone   -> get_this_week_task('1');
        if($taskDaily){
            $daily = 1 ; //已领取
        }else{
            $daily = 2 ; //未领取
        }
        foreach($taskDaily as $k => $v){
            $status[] = $taskDaily[$k]['status'];
        }
        if(in_array('1',$status)){
            $extra = 1; //不可领取;
        }else{
            $extra = 2; //可领取
        }
        $this -> assign('daily',$daily) ;
        $this -> assign('extra',$extra) ;
        $this -> assign('moneyOne',$moneyOne);
        $this -> assign('moneyTwo',$moneyTwo);
        $this -> assign('weeklyTypeOne',$weeklyTypeOne);
        $this -> assign('weeklyTypeTwo',$weeklyTypeTwo);
        $this -> display();
    }


    /**领取任务**/
    public function taskOfficeDetail($method=null){
        $dbTaskDone = D('TaskDone');
        $dbTaskWeekly = D('TaskWeekly');
        $taskSet = $dbTaskDone -> get_this_week_task('1');
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
                    $weeklyTypeOne = $dbTaskWeekly -> get_weekly_type('1');
                    foreach($weeklyTypeOne as $k => $v){
                        $data['task_id']    = $weeklyTypeOne[$k]['task_id'];
                        $data['inneed']     = $weeklyTypeOne[$k]['inneed'];
                        $dbTaskDone -> add_done($data);
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
                        $dbTaskDone->add_done($data);
                    }
                }
                break;
        }
        //上面是对是否插入数据进行判断，下面是展示taskOfficeDetail页面
        $taskDoneDaily      = $dbTaskDone   -> get_this_week_task('1');
        $taskDoneExtra      = $dbTaskDone   -> get_this_week_task('2');
        $this -> assign('taskDoneDaily',$taskDoneDaily);
        $this -> assign('taskDoneExtra',$taskDoneExtra);
        $this -> display();
    }


    /**日常提交任务**/
    public function taskSubmit(){
        //思路
        //任务完成的情况下：
            // 1.将表中日常任务的状态值从1改成2
            // 2.将给用户增加金额
        //任务过期未完成的情况下：
            // 1.将表中日常任务的状态值从1改成3
    }

















}
