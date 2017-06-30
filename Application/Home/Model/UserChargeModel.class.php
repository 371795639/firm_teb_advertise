<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class UserChargeModel extends Model{

    /**
     * 获取本周内的玩家首次充值数量/玩家充值列表
     * @param $game_id  integer 玩家ID
     * @param $count    integer 0：返回数量；1：返回列表
     * @return bool|mixed|string    $number：玩家数量；$res：玩家充值列表
     */
    public function get_user_first_charge($game_id,$count){
        $TaskWeekly = D('TaskWeekly');
        $date       = date('Y-m-d H:i:s');
        $start      = $TaskWeekly -> get_start_time($date);
        $end        = $TaskWeekly -> get_end_time($date);
        $where      = array(
            'create_time'   => array(array('gt',$start),array('lt',$end)),
            'is_first'      => 1,
        );
        if(is_array($game_id)){
            $where['game_id'] = array('in',$game_id);
        }else{
            $where['game_id'] = $game_id;
        }
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



}
