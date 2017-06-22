<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskDoneModel extends Model{

    /*
     * 根据id查找任务
     * @param  integer  $type    任务类型
     * @return array    $re      查找的数据
     */
    public function get_done_by_id($id){
        $re = $this -> where(array('type'=>(int)$id)) -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    public function add_done($data){
        if(!empty($data)) {
            $re = $this->add($data);
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }



}
