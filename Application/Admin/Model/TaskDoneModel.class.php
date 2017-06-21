<?php

namespace Admin\Model;
use Think\Model;

class TaskDoneModel extends Model {
    /*
     * 根据ID查找已完成任务
     * @param  integer  $id     已完成任务的ID
     * @return array    $re     查找的数据
     */
    public function get_done_by_id($id){
        $re = $this -> where(array('id'=>(int)$id)) -> find();
        if($re){
           return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据task_id更新数据
     * @param  integer  $task_id    任务ID
     * @param  array    $data       要更新的数据
     * @return array    $re         插入的数据
     */
    public function save_done_by_id($task_id,$data){
        $re = $this -> where(array('task_id'=>(int)$task_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 获取已完成任务列表
     * @return  array   $re     任务列表
     */
    public function get_all_done(){
        $re = $this -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据已完成任务的ID删除任务
     * @param   array   $id    周任务ID
     * @return  array   $re
     */
    public function delete_done_by_id($id){
        $re = $this -> where(array('id'=>(int)$id)) -> delete();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

























}
