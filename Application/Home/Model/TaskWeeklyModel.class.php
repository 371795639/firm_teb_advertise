<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskWeeklyModel extends Model{

    /*
     * 根据任务的起始时间和类型查找任务
     * @param  integer  $type       任务类型
     * @param  data     $start_time 开始时间
     * @param  data     $end_time   结束时间
     * @return array    $re         查找的数据
     */
    public function get_weekly_by_type($type){
        $start_time = date('Y-m-d 02:00:00',strtotime('Monday'));  //TODO 待定
        $ss = strtotime($start_time);
        $end_time = date('Y-m-d 23:59:59',strtotime('Sunday',$ss));
        $re = $this -> where(array('type'=>(int)$type,'start_time'=>$start_time,'end_time'=>$end_time)) -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据发布的起始时间时间查找任务
     * @param  date     $start_time    任务类型
     * @param  date     $end_time      任务类型
     * @return array    $re            查找的数据
     */
    public function get_weekly_by_time(){
        $start_time = date('Y-m-d 02:00:00',strtotime('Monday'));  //TODO 待定
        $ss = strtotime($start_time);
        $end_time = date('Y-m-d 23:59:59',strtotime('Sunday',$ss));
        $re = $this -> where(array('start_time'=>$start_time,'end_time'=>$end_time)) -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 获取下周任务的基础上获取任务类型
     * @param $type   integer  任务类型
     * @return mixed    任务类型对应的数组
     */
    public function get_weekly_type($type){
        $dbTask = D('Task');
        $taskWeekly = $this -> get_weekly_by_time();
        foreach ($taskWeekly as $k => $v) {
            $task_id = $taskWeekly[$k]['task_id'];
            $resTask = $dbTask -> get_task_by_id($task_id);
            $taskWeekly[$k]['money']        = $resTask['money'];
            $taskWeekly[$k]['type']         = $resTask['type'];
            $taskWeekly[$k]['inneed']       = $resTask['inneed'];
            $taskWeekly[$k]['is_game']      = $resTask['is_game'];
            $taskWeekly[$k]['tasker']       = $resTask['tasker'];
            $taskWeekly[$k]['create_time']  = $resTask['create_time'];
        }
        foreach($taskWeekly as $key => $val){
            if($val['type'] == $type){
                $taskWeeklyTypeOne[$key] = $val;
            }
        }
        return $taskWeeklyTypeOne;
    }


}
