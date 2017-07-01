<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class NoticeModel extends Model{

    /**
     * 根据任务类型查找数据，以倒序形式展现
     * @param $type     integer     任务类型=>1：系统公告消息；2：活动提醒消息；3：任务提醒消息；
     * @return bool|mixed   array   $re
     */
    public function get_notice_by_type($type){
        $re = $this -> where(array('notice_type_id' => (int)$type)) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

}
