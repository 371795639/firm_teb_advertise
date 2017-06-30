<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class StaffModel extends Model{
    /*
     * 获取推荐人是同一推广专员的数量
     * @param  integer  $uid    推广专员ID
     * @return integer  $res    数量
     */
    public function count_staff_by_referee($referee){
        $re = $this -> where(array('referee'=>(int)$referee)) -> select();
        $res = count($re) == 0 ? 0 :count($re);
        if($res){
            return $res;
        }else{
            return false;
        }
    }


    /*
     * 根据ID查找推广专员
     * @param  integer  $id     推广专员ID
     * @return array    $re     查找的数据
     */
    public function get_staff_by_id($id){
        $re = $this -> where(array('id'=>(int)$id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据ID更新数据
     * @param  integer  $id    推广专员ID
     * @param  array    $data   要更新的数据
     * @return array    $re     插入的数据
     */
    public function save_staff_by_id($id,$data){
        $re = $this -> where(array('id'=>(int)$id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取推广专员列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     推广专员列表
     */
    public function get_all_task($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    public function get_staff_league($id){
        $league = $this -> get_staff_by_id($id);
        $is_league = $league['is_league'];
        if($is_league == 0){
            $class = 0;
        }else{
            $staffInfo = D('StaffInfo');
            $cla = $staffInfo -> get_staff_by_uid($id);
            if($cla){
                $class = $cla['class'];
            }else{
                return false;
            }
        }
        return $class;
    }

}
