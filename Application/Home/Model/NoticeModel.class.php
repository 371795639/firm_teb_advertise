<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class NoticeModel extends Model{

    /**
     * 根据ID获取消息
     * @param $id       integer 消息ID
     * @param $search   string  搜索类型：find/select
     * @return mixed|string     array
     */
    public function get_notice_by_id($id,$search){
        switch($search){
            case 'find':
                $re = $this -> where(array('id' => (int)$id)) -> order('id DESC') -> find();
                break;
            case 'select':
                $re = $this -> where(array('id' => (int)$id)) -> order('id DESC') -> select();
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


    /**
     * 根据任务类型查找数据，以倒序形式展现
     * @param $type     integer     消息类型=>1：系统公告消息；2：活动提醒消息；3：任务提醒消息；
     * @param $uid      integer     用户ID ID为0则是管理员发布的公告
     * @param $kind     integer     公告种类1：管理员发布的公告；2：系统提示消息
     * @return bool|mixed   array   $re
     */
    public function get_notice_by_type($type,$uid,$kind){
        if($uid){
            $map['uid'] = $uid;
        }
        if($kind){
            $map['kind'] = $kind;
        }
        if($type){
            $map['notice_type_id'] = $type;
        }else{
            $map['notice_type_id'] = array('in','1,2,3,4');
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
     * @param $uid      integer     用户ID ID为0则是管理员发布的公告
     * @param $kind     integer     公告种类1：管理员发布的公告；2：系统提示消息
     * @return bool|mixed   array   $re
     */
    public function get_notice_by_type_time_format($type,$uid,$kind){
        $re = $this -> get_notice_by_type($type,$uid,$kind);
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
     * 根据类型返回表中未读消息的条数
     * @param $type     integer 消息类型
     * @param $uid      integer     用户ID ID为0则是管理员发布的公告
     * @param $kind     integer     公告种类1：管理员发布的公告；2：系统提示消息
     * @return int      integer 数量
     */
    public function count_notice_by_type($type,$uid,$kind){
        $re = $this -> get_notice_by_type($type,$uid,$kind);
        if($re == null){
            $total = 0;
        }else{
            $total = count($re);
        }
        $count = 0;
        foreach($re as $item){
            $ids = explode(',',$item['id_read']);
            if(in_array($_SESSION['userid'],$ids)){
                $count ++;
            }
        }
        $number = $total - $count;
        return $number;
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
     * 设置未读消息成已读
     * @param $type integer 消息类型
     * @return bool true：更新成功；false：更新失败
     */
    public function set_is_read($type,$uid,$kind){
        $re = $this -> get_notice_by_type($type,$uid,$kind);
        foreach($re as $k => $v){
            $notice_id = $re[$k]['id'];
            $ids = explode(',',$re[$k]['id_read']);
            if(!in_array($_SESSION['userid'],$ids)){
                $data['id_read'] = $re[$k]['id_read'].$_SESSION['userid'].',';
                $res = $this -> save_notice('id',$notice_id,$data);
            }
        }
        if($res){
            return true;
        }else{
            return false;
        }
    }









}
