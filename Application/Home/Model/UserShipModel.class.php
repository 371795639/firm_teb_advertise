<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class UserShipModel extends Model{

    /**
     * 获取所有recommend是同一值的玩家
     * @param $id   integer     加盟商ID
     * @param $what     integer     count：返回玩家数量；select：返回玩家列表
     * @return bool|mixed       玩家列表
     */
    public function get_user_by_recommend($id,$what){
        $res = $this -> where(array('recommend'=>$id)) -> select();
        switch($what){
            case 'count':
                $number = $res == 0 ? 0 : count($res);
                return $number;
                break;
            case 'select':
                return $res;
                break;
            default:
                return '参数错误';
        }
    }


}
