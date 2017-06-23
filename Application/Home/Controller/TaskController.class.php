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
        $taskDaily      = $dbTaskDone   -> get_this_week_task();
        if($taskDaily){
            $daily = "<span class='right geted'>查 看</span>";
        }else{
            $daily = "<span class='right get'>未领取</span>";
        }
        //done 表中
        foreach($taskDaily as $k => $v){
            $status[] = $taskDaily[$k]['status'];
        }
        if(in_array('1',$status)){
            $extra = 1;//不可领取;
        }else{
            $extra = 2;//可领取
        }
        $this -> assign('daily',$daily) ;
        $this -> assign('extra',$extra) ;
        $this -> assign('moneyOne',$moneyOne);
        $this -> assign('moneyTwo',$moneyTwo);
        $this -> assign('weeklyTypeOne',$weeklyTypeOne);
        $this -> assign('weeklyTypeTwo',$weeklyTypeTwo);
        //因为必须先完成日常任务才能领取额外任务，所以在领取日常任务之前判断本周内是否有任务是可以实现的
        $this -> display();
    }


    /**领取任务**/
    public function taskOfficeDetail($method=null){
        $dbTaskDone = D('TaskDone');
        $dbTaskWeekly = D('TaskWeekly');
        switch($method){
            case 'daily':
                $taskSet = $dbTaskDone -> get_this_week_task();
                if($taskSet){
                    //noting need to do here.
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
                $weeklyDone = $dbTaskDone -> get_done_by_task_id();
                $resDone = $dbTaskDone -> i_array_column($weeklyDone,'task_id');
                $task_id = I('task_id');
                if(in_array($task_id,$resDone)){
                    //nothing need to do here.
                }else {
                    $resStaff =  D('Task') -> get_task_by_id($task_id);
                    $data = array(
                        'task_id'   => $task_id,
                        'uid'       => $_SESSION['userid'],
                        'inneed'    => $resStaff['inneed'],
                        'get_time'  => date('Y-m-d H:i:s'),
                        'done_time' => '',  //不可用null，否则无法插入数据
                        'status'    => 1,
                    );
                    $dbTaskDone->add_done($data);//放止重复提交
                }
                break;
        }
        $this -> display();
    }



















}
