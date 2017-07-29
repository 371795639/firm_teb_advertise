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
        $res = count($re) == 0 ? 0 : count($re);
        if($res){
            return $res;
        }else{
            return false;
        }
    }


    /*
     * 获取推荐人是同一推广专员的数量
     * @param  integer  $referee    推荐人ID
     * @param  integer  $what       返回方式
     * @param  integer  $ids        返回ID
     * @return integer
     */
    public function get_staff_by_referee($referee,$what,$ids){
        $map['is_league'] = 0;
        $field = 'id,staff_real,mobile,is_league';
        if(is_array($referee)){
            $map['referee'] = array('in',$referee);
        }else{
            $map['referee'] = $referee;
        }
        switch (strtolower($what)){
            case 'find':
                $re = $this ->field($field)-> where($map) -> order('id ASC') -> find();
                break;
            case 'select':
                $re = $this ->field($field)-> where($map) -> order('id ASC') -> select();
                break;
            default :
                $re = '参数错误';
        }
        if($re){
            if(isset($ids)){
                $tgIds = [];
                foreach($re as $k => $v){
                    $tgIds[] = $re[$k]['id'];
                }
//                $tgIds = implode(',',$tgIds);
                return $tgIds;
            }else{
                return $re;
            }
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


    /**
     * 根据ID获取加盟商的等级
     * @param $id   integer
     * @return bool|int     加盟商等级  ->等级为0，不是加盟商
     */
    public function get_staff_league($id){
        $league     = $this -> get_staff_by_id($id);
        $is_league  = $league['is_league'];
        if($is_league == 0){
            $class = 0;
        }else{
            $staffInfo  = D('StaffInfo');
            $cla        = $staffInfo -> get_staff_by_uid($id);
            if($cla){
                $class = $cla['class'];
            }else{
                return false;
            }
        }
        return $class;
    }


    /**获取表中加盟商某一字段的所有值
     * @param $id   string  字段名
     * @return mixed
     */
    public function get_all_staff_key($id){
        $re = $this  -> where(array("is_league" => 1)) ->select();
        if($re){
            if(empty($id)){
                return $re;
            }else{
                foreach($re as $k => $v){
                    $ids[] = $re[$k][$id];
                }
                return $ids;
            }
        }else{
            return false;
        }
    }
}
