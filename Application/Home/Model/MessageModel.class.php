<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class MessageModel extends Model{
    /*
     * 根据ID查找反馈
     * @param  integer  $msg_id    反馈ID
     * @return array    $re        查找的数据
     */
    public function get_msg_by_id($msg_id){
        $re = $this -> where(array('id'=>(int)$msg_id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据ID更新数据
     * @param  integer  $msg_id    反馈ID
     * @param  array    $data      要更新的数据
     * @return array    $re        插入的数据
     */
    public function save_msg_by_id($msg_id,$data){
        $re = $this -> where(array('id'=>(int)$msg_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取反馈列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     反馈列表
     */
    public function get_all_msgs($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /**
     * 根据反馈ID删除反馈，同时删除回复表中对此反馈的回复
     * @param $msg_id
     * @return bool
     */
    public function del_reply_by_id($msg_id){
        $re = $this -> where(array('id'=>(int)$msg_id)) -> limit('1') -> delete();
        $dbMsgRe = D('MessageReply');
        $res = $dbMsgRe -> del_reply_by_msgid($msg_id);
        if($re && $res){
            return true;
        }else{
            return false;
        }
    }


}
