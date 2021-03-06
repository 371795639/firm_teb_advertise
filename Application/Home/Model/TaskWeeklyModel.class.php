<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskWeeklyModel extends Model{

    /**
     * 获取$data时间所在周一
     * @param $date
     * @return bool|string
     */
    public function get_start_time($date){
        if(!empty($date)) {
            $time = strtotime($date);
            $week = date('N', $time);
            if ($week == 1) {
                $start_time = date('Y-m-d 02:00:00', strtotime(' monday', $time));
            } else {
                $start_time = date('Y-m-d 02:00:00', strtotime('-1 monday', $time));
            }
        }else{
            $start_time = "";
        }
        return $start_time;
    }


    /**
     * 获取$data时间所在周末
     * @param $date
     * @return bool|string
     */
    public function get_end_time($date){
        if(empty($date)) {
            $end_time   = "";
        }else{
            $start_time = $this->get_start_time($date);
            $ss         = strtotime($start_time);
            $end_time   = date('Y-m-d 23:59:59', strtotime('Sunday', $ss));
        }
        return $end_time;
    }


    /*
     * 根据任务的起始时间和类型查找任务
     * @param  integer  $type       任务类型
     * @param  data     $start_time 开始时间
     * @param  data     $end_time   结束时间
     * @return array    $re         查找的数据
     */
    public function get_weekly_by_type($type){
        $date       = date('Y-m-d H:i:s');
        $start_time = $this -> get_start_time($date);
        $end_time   = $this -> get_end_time($date);
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
//        $date       = '2017-07-08 15:55:55';
        $date       = date('Y-m-d H:i:s');
        $start_time = $this -> get_start_time($date);
        $end_time   = $this -> get_end_time($date);
        $re = $this -> where(array('start_time'=>$start_time,'end_time'=>$end_time)) -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 获取下周任务的基础上获取任务类型和任务等级
     * @param   $type   integer  任务类型
     * @param   $class  integer  任务等级
     * @return  mixed   任务类型对应的数组
     */
    public function get_weekly_type($type,$class){
        $dbTask = D('Task');
        $taskWeekly = $this -> get_weekly_by_time();
        if($taskWeekly) {
            foreach ($taskWeekly as $k => $v) {
                $task_id = $taskWeekly[$k]['task_id'];
                $resTask = $dbTask -> get_task_by_id($task_id);
                $taskWeekly[$k]['money']        = $resTask['money'];
                $taskWeekly[$k]['type']         = $resTask['type'];
                $taskWeekly[$k]['class']        = $resTask['class'];
                $taskWeekly[$k]['detail']       = $resTask['detail'];
                $taskWeekly[$k]['inneed']       = $resTask['inneed'];
                $taskWeekly[$k]['is_game']      = $resTask['isgame'];
                $taskWeekly[$k]['tasker']       = $resTask['tasker'];
                $taskWeekly[$k]['create_time']  = $resTask['create_time'];
            }
            if(empty($type)){
                return $taskWeekly;
            }else{
                foreach ($taskWeekly as $key => $val) {
                    if ($val['type'] == $type) {
                        $taskWeeklyType[$key] = $val;
                    }
                }
            }
            if(empty($class)){
                return $taskWeeklyType;
            }else {
                foreach ($taskWeeklyType as $k => $v) {
                    if ($v['class'] == $class) {
                        $taskWeeklyClass[$k] = $v;
                    }
                }
            }
            return $taskWeeklyClass;
        }else{
            return false;
        }
    }


    /**
     * 根据任务类型，获取周任务总金额
     * @param   $type   integer  任务类型
     * @param   $class  integer  任务等级
     * @return string
     */
    public function get_weekly_money($type,$class){
        $taskWeekly = $this -> get_weekly_type($type,$class);
        $money = '';
        foreach($taskWeekly as $k => $v){
            $money += $taskWeekly[$k]['money'];
        }
        return $money;
    }

    /**根据条件搜索周任务列表
     * @param $map  array   搜索条件
     * @return bool|mixed
     */
    public function get_weekly_by_map($map){
        if($map){
            $re = $this -> where($map) -> select();
        }
        if(empty($map)){
            return false;
        }else{
            return $re;
        }
    }
}
