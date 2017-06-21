<?php

namespace Home\Controller;
use Think\Controller;

class TaskController extends HomeController {

    public function index(){

    }

    /**
     * 引入前台首页Application/Home/View/tuiguang/Index/index.html
     * 引入前台Feed控制器，控制器文件未修改，等待前台页面的设计，Feed表未建
     * 引入前台任务页面，功能正在完善中
     */
    // http://www.ottg.com.cn/index.php?s=/home/task/taskoffice.html

    /**加载任务大厅页面**/
    public function taskOffice(){
        $dbTask = D('Task');
        $dbTaskWeekly = D('TaskWeekly');
        $TaskWeekly = $dbTaskWeekly -> get_weekly_by_time();//获取下周任务
        foreach ($TaskWeekly as $k => $v) {
            $task_id = $TaskWeekly[$k]['task_id'];
            $resTask = $dbTask -> get_task_by_id($task_id);
            $TaskWeekly[$k]['type'] = $resTask['type'];
        }
//        p($TaskWeekly);
        $this -> assign('TaskWeekly',$TaskWeekly);
        $this -> display();
    }


    public function taskOfficeDetail(){
        $this -> display();
    }



















}
