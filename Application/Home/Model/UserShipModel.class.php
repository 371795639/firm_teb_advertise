<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class UserShipModel extends Model{


    /**
     * 获取本周内superior是同一值的玩家/玩家数量
     * @param $id       integer     加盟商ID
     * @param $count    integer     0：返回玩家数量；1：返回玩家列表
     * @return int/array    $number：玩家数量；$res：玩家列表
     */
    public function count_user_by_superior($id,$count){
        $TaskWeekly = D('TaskWeekly');
        $date       = date('Y-m-d H:i:s');
        $start      = $TaskWeekly -> get_start_time($date);
        $end        = $TaskWeekly -> get_end_time($date);
        $where      = array(
            'superior' => $id,
            'reg_time' => array(array('gt',$start),array('lt',$end)),
        );
        $res = $this -> where($where) -> select();
        switch($count){
            case '0':
                $number = $res == 0 ? 0 : count($res);
                return $number;
            break;
            case '1':
                return $res;
            break;
            default:
                return '参数错误';
        }
    }


    /**
     * 获取所有superior是同一值的玩家
     * @param $id   integer     加盟商ID
     * @return bool|mixed       玩家列表
     */
    public function get_user_by_superior($id){
        $res = $this -> where(array('superior'=>(int)$id)) -> select();
        if($res){
            return $res;
        }else{
            return false;
        }
    }


}
