<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    public function index(){

    }


    /**加载任务大厅页面**/
    public function taskOffice(){
        $dbTask = D('Task');
        $dbTaskWeekly = D('TaskWeekly');
        $taskWeekly = $dbTaskWeekly -> get_weekly_by_time();//获取下周任务
        foreach ($taskWeekly as $k => $v) {
            $task_id = $taskWeekly[$k]['task_id'];
            $resTask = $dbTask -> get_task_by_id($task_id);
            $taskWeekly[$k]['type'] = $resTask['type'];
            $taskWeekly[$k]['money'] = $resTask['money'];
        }
        $this -> assign('taskWeekly',$taskWeekly);
        $this -> display();
    }


    public function taskOfficeDetail(){
        $this -> display();
    }



















}
