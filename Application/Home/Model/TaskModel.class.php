<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskModel extends Model{
    /*
     * 根据ID查找任务
     * @param  integer  $task_id    任务ID
     * @return array    $re         查找的数据
     */
    public function get_task_by_id($task_id){
        $re = $this -> where(array('id'=>(int)$task_id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据ID更新数据
     * @param  integer  $task_id    任务ID
     * @param  array    $data       要更新的数据
     * @return array    $re         插入的数据
     */
    public function save_task_by_id($task_id,$data){
        $re = $this -> where(array('id'=>(int)$task_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取任务列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     任务列表
     */
    public function get_all_task($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

}
