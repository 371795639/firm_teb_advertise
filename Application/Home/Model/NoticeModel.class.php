<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class NoticeModel extends Model{

    /**
     * 根据任务类型查找数据，以倒序形式展现
     * @param $type     integer     消息类型=>1：系统公告消息；2：活动提醒消息；3：任务提醒消息；
     * @param $read     integer     是否已读=>1：未读；2：已读；
     * @return bool|mixed   array   $re
     */
    public function get_notice_by_type($type,$read){
        if($type){
            $map['notice_type_id'] = $type;
        }
        if($read){
            $map['is_read'] = $read;
        }
        $re = $this -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 根据任务类型查找数据，以倒序形式展现，并对时间进行处理，如果是当天发布，只显示时间，否则显示日期
     * @param $type     integer     消息类型=>1：系统公告消息；2：活动提醒消息；3：任务提醒消息；
     * @param $read     integer     是否已读=>1：未读；2：已读；
     * @return bool|mixed   array   $re
     */
    public function get_notice_by_type_time_format($type,$read){
        $re = $this -> get_notice_by_type($type,$read);
        $date = date('Y-m-d');
        foreach($re as $k => $v){
            if(time_formatiss($re[$k]['create_time']) == $date){
                $re[$k]['create_time'] = time_formatsss($re[$k]['create_time']);
            }else{
                $re[$k]['create_time'] = time_formatiss($re[$k]['create_time']);
            }
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 根据类型返回表中未读/已读消息的条数
     * @param $type integer 消息类型
     * @param $read integer 是否已读 1：未读；2：已读
     * @return int  integer 数量
     */
    public function count_notice_by_type($type,$read){
        $re = $this -> get_notice_by_type($type,$read);
        if($re){
            $res = count($re) == 0 ? 0 :count($re);
        }else{
            $res = 0;
        }
        return $res;
    }


    /**
     * 根据key、value更新数据
     * @param $key      string      字段名
     * @param $value    string      字段值
     * @param $data     array       要更新的数据
     * @return bool     true：更新成功；false：更新失败
     */
    public function save_notice($key,$value,$data){
        $re = $this -> where(array($key => $value)) -> save($data);
        if($re){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 设置消息成已读或未读
     * @param $type     integer     消息类型
     * @param $read     integer     要更新的状态
     * @param $newRead  integer     更新后的状态
     * @return bool     true：更新成功；false：更新失败
     */
    public function set_is_read($type,$read,$newRead){
        $re = $this -> get_notice_by_type($type,$read);
        foreach($re as $k => $v){
            if($re[$k]['is_read'] == $read){
                $id = $re[$k]['id'];
                $data['is_read']  = $newRead;
                $res = $this -> save_notice('id',$id,$data);
            }
        }
        if($res){
            return true;
        }else{
            return false;
        }
    }




















}
