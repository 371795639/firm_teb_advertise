<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskWeeklyModel extends Model{

    /*
     * 根据类型查找任务
     * @param  integer  $type    任务类型
     * @return array    $re      查找的数据
     */
    public function get_weekly_by_type($type){
        $re = $this -> where(array('type'=>(int)$type)) -> select();
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
        $end_time = date('Y-m-d 11:59:59',strtotime('Sunday',$ss));
        $re = $this -> where(array('start_time'=>$start_time,'end_time'=>$end_time)) -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

}
