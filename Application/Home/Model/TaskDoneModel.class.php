<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskDoneModel extends Model{

    /**
     * 根据字段和值，查找已完成任务
     * @param $key          string    字段
     * @param $value        integer     值
     * @param null $what    string      find：查询一条；select：查询所有
     * @return bool|mixed|null
     */
    public function get_done_by_uid($key,$value,$what=null){
        if($what == 'find'){
            $re = $this -> where(array($key => (int)$value)) -> find();
        }elseif($what == 'select') {
            $re = $this -> where(array($key => (int)$value)) -> select();
        }else{
            $re = null;
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    public function get_done_by_task_id(){
        $re = $this -> field('task_id') -> select();
        return $re;
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    public function add_done($data){
        if(!empty($data)) {
            $re = $this->add($data);
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**
     * 获取$data时间所在周一
     * @param $date
     * @return bool|string
     */
    public function get_start_time($date){
        if(!empty($date)) {
            $time = strtotime($date);
            $week = date('N', $time);
            if ($week == 1) {
                $start_time = date('Y-m-d 02:00:00', strtotime(' monday', $time));
            } else {
                $start_time = date('Y-m-d 02:00:00', strtotime('-1 monday', $time));
            }
        }else{
            $start_time = "";
        }
        return $start_time;
    }


    /**
     * 获取$data时间所在周末
     * @param $date
     * @return bool|string
     */
    public function get_end_time($date){
        if(empty($date)) {
            $end_time = "";
        }else{
            $start_time = $this->get_start_time($date);
            $ss = strtotime($start_time);
            $end_time = date('Y-m-d 23:59:59', strtotime('Sunday', $ss));
        }
        return $end_time;
    }


    /**
     * 获取这周时间内的任务
     */
    public function get_this_week_task(){
        $date = date('Y-m-d H:i:s');
        $start_time = $this -> get_start_time($date);
        $end_time = $this -> get_end_time($date);
        $map['get_time'] = array(array('gt', $start_time), array('lt', $end_time));
        $map['uid'] = $_SESSION['userid'];
        $res = $this -> where($map) -> select();//有值就说明已经领取过了
        if($res){
           return $res;
        }else{
            return false;
        }
    }


    function i_array_column($input, $columnKey, $indexKey=null){
        if(!function_exists('array_column')){
            $columnKeyIsNumber  = (is_numeric($columnKey))?true:false;
            $indexKeyIsNull            = (is_null($indexKey))?true :false;
            $indexKeyIsNumber     = (is_numeric($indexKey))?true:false;
            $result                         = array();
            foreach((array)$input as $key=>$row){
                if($columnKeyIsNumber){
                    $tmp= array_slice($row, $columnKey, 1);
                    $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
                }else{
                    $tmp= isset($row[$columnKey])?$row[$columnKey]:null;
                }
                if(!$indexKeyIsNull){
                    if($indexKeyIsNumber){
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key))?current($key):null;
                        $key = is_null($key)?0:$key;
                    }else{
                        $key = isset($row[$indexKey])?$row[$indexKey]:0;
                    }
                }
                $result[$key] = $tmp;
            }
            return $result;
        }else{
            return array_column($input, $columnKey, $indexKey);
        }
    }
}
