<?php

namespace Admin\Model;
use Think\Model;
use User\Api\UserApi;

class UserChargeModel extends Model{

    /**
     * 获取本周内的玩家首次充值数量/玩家充值列表
     * @param $game_id  integer 玩家ID
     * @param $first    integer 玩家ID
     * @param $what     integer count：返回数量；select：返回列表
     * @return bool|mixed|string    $number：玩家数量；$res：玩家充值列表
     */
    public function get_user_charge($game_id,$first,$what){
        if($first == 1){
            $where['is_first'] = 1;
        }elseif($first == 0){
            $where['is_first'] = 0;
        }elseif($first == 2){
            $where['is_first'] = array('in','0,1');
        }
        if(is_array($game_id)){
            $where['game_id'] = array('in',$game_id);
        }else{
            $where['game_id'] = $game_id;
        }
        $res = $this -> where($where) -> select();
        $total = $this -> where($where) -> field('money') -> sum('money');
        switch($what){
            case 'count':
                $number = $res == 0 ? 0 : count($res);
                return $number;
                break;
            case 'select':
                return $res;
                break;
            case 'money':
                $money = $total == 0 ? 0 : $total;
                return $money;
                break;
            default:
                return '参数错误';
        }
    }



}
