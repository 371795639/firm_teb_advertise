<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class FlowModel extends Model{

    /**
     * 根据用户ID获取流水信息
     * @param $uid      integer 用户ID
     * @param $search   string  搜索类型：find/select
     * @return mixed|string     array
     */
    public function get_flow_by_uid($uid,$search){
        switch($search){
            case 'find':
                $re = $this -> where(array('uid' => (int)$uid)) -> order('id DESC') -> find();
                break;
            case 'select':
                $re = $this -> where(array('uid' => (int)$uid)) -> order('id DESC') -> select();
                break;
            default:
                $re = '参数错误';
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }



}
