<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class UserShipModel extends Model{


    public function get_user_by_superior(){
        $TaskWeekly   = D('TaskWeekly');
        $date = date('Y-m-d H:i:s');
        $start  = $TaskWeekly -> get_start_time($date);
        $end    = $TaskWeekly -> get_end_time($date);
        $where = array(
            'superior' => $_SESSION['userid'],
            'reg_time' => array(array('gt',$start),array('lt',$end)),
        );
        $res = $this -> where($where) -> select();
        if(empty($res)){
            $ress = 0;
        }else{
            $ress = count($res);
        }
        return $ress;
    }


}
