<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class MessageReplyModel extends Model{
    /*
     * 根据ID查找回复
     * @param  integer  $reply_id  回复ID
     * @return array    $re        查找的数据
     */
    public function get_reply_by_id($reply_id){
        $re = $this -> where(array('id'=>(int)$reply_id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据ID查找回复
     * @param  integer  $msgid  反   馈ID
     * @return array    $re        查找的数据
     */
    public function get_reply_by_msgid($msgid){
        $re = $this -> where(array('msg_id'=>(int)$msgid)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据ID更新数据
     * @param  integer  $msgid      回复ID
     * @param  array    $data      要更新的数据
     * @return array    $re        插入的数据
     */
    public function save_reply_by_msgid($msgid,$data){
        $re = $this -> where(array('msg_id'=>(int)$msgid)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据ID更新数据
     * @param  integer  $reply_id  回复ID
     * @param  array    $data      要更新的数据
     * @return array    $re        插入的数据
     */
    public function save_reply_by_id($reply_id,$data){
        $re = $this -> where(array('id'=>(int)$reply_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据条件获取回复列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     回复列表
     */
    public function get_all_replys($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * @param $msgid    integer   反馈ID
     * @return bool
     */
    public function del_reply_by_msgid($msgid){
        $re = $this -> where(array('msg_id'=>(int)$msgid)) -> delete();
        if($re){
            return true;
        }else{
            return false;
        }
    }
}
