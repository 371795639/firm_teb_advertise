<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    public function index(){

    }


    /**加载任务大厅页面**/
    public function taskOffice(){
        $dbTaskWeekly = D('TaskWeekly');
        $weeklyTypeOne = $dbTaskWeekly -> get_weekly_type('1');
        $weeklyTypeTwo = $dbTaskWeekly -> get_weekly_type('2');
        $this -> assign('weeklyTypeOne',$weeklyTypeOne);
        $this -> assign('weeklyTypeTwo',$weeklyTypeTwo);
        $this -> display();
    }


    /**领取任务**/
    public function taskOfficeDetail($method = null){
        $dbTaskDone = D('TaskDone');
        $dbTaskWeekly = D('TaskWeekly');
        $data = array(
            'uid'       => 1,   //从session中获取
            'get_time'  => date('Y-m-d H:i:s'),
            'done_time' => '',  //不可用null，否则无法插入数据
            'status'    => 1,
        );
        switch($method){
            case 'daily':
                $weeklyTypeOne = $dbTaskWeekly -> get_weekly_type('1');
                foreach($weeklyTypeOne as $k => $v){
                    $data['task_id']    = $weeklyTypeOne[$k]['task_id'];
                    $data['inneed']     = $weeklyTypeOne[$k]['inneed'];
//                    $dbTaskDone -> add_done($data);
                }
                echo 123;
                $this -> assign('weeklyTypeOne',$weeklyTypeOne);
                break;
            case 'extra':
                echo 123;
                //href="{:U('Home/Task/taskOfficeDetail',array('method'=>'extra'))}"
                break;
        }


        $this -> display();
    }



















}
