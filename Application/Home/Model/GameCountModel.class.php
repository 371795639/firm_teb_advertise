<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class GameCountModel extends Model{

    /**
     * 根据字段和值，查找数据
     * @param $key          string    字段
     * @param $value        integer    值
     * @param null $what    string     find：查询一条；select：查询所有
     * @return bool|mixed|null
     */
    public function get_count_by_uid($key,$value,$what=null){
        if($what == 'find'){
            $re = $this -> where(array($key => $value)) -> find();
        }elseif($what == 'select') {
            $re = $this -> where(array($key => $value)) -> select();
        }else{
            $re = null;
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 获取$dateCount所在的周内的
     * @param $uid  integer 用户uid
     * @param $what string  find：返回单条数据；select返回所有数据
     * @return mixed|string
     */
    public function get_game($uid,$what){
        $dateCount  = date('Y-m-d H:i:s');
        $dbtaskDone = D('TaskDone');
        $start      = $dbtaskDone -> get_start_time($dateCount);
        $end        = $dbtaskDone -> get_end_time($dateCount);
        $map['time']= array(array('gt', $start), array('lt', $end));
        if($uid){
            $map['uid'] = $uid;
        }
        switch($what){
            case 'find':
                $re = $this -> where($map) -> order('id DESC') -> find();
                break;
            case 'select':
                $re = $this -> where($map) -> order('id DESC') -> select();
                break;
            default :
                $re = '参数错误';
        }
        return $re;

    }


    /**根据$key，$value更新信息
     * @param $key      string  字段名
     * @param $value    string  字段值
     * @param $data     array   要更新的数据
     * @return bool     true：更新成功；false：更新失败
     */
    public function save_game($key,$value,$data){
        $re = $this -> where(array($key => $value)) -> save($data);
        if($re){
            return true;
        }else{
            return false;
        }
    }
}
