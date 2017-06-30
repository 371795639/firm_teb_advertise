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
            $re = $this -> add($data);
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
     * @param $date time    当前时间
     * @return bool|string
     */
    public function get_end_time($date){
        if(empty($date)) {
            $end_time = "";
        }else{
            $start_time = $this -> get_start_time($date);
            $ss         = strtotime($start_time);
            $end_time   = date('Y-m-d 23:59:59', strtotime('Sunday', $ss));
        }
        return $end_time;
    }

    /**
     * 根据用户获取本周内所有已领取的任务
     * @return mixed
     */
    public function get_this_week_all_task(){
        $date = date('Y-m-d H:i:s');
        $start_time = $this -> get_start_time($date);
        $end_time = $this -> get_end_time($date);
        $map['get_time']    = array(array('gt', $start_time), array('lt', $end_time));
        $map['uid']         = $_SESSION['userid'];
        $res = $this -> where($map) -> select();//有值就说明已经领取过了
        foreach($res as $k => $v){
            $task_id = $res[$k]['task_id'];
            $task_ids = D('Task') -> get_task_by_id($task_id);
            $res[$k]['type']    = $task_ids['type'];
            $res[$k]['money']   = $task_ids['money'];
            $res[$k]['inneed']  = $task_ids['inneed'];
            $res[$k]['name']    = $task_ids['name'];
        }
        return $res;
    }


    /**
     * 在task_done表中根据get_time和uid获取任务类型
     * @param   $type integer 任务类型 1：日常任务 2：额外任务
     * @return  bool
     */
    public function get_this_week_task($type){
        $resDone = $this -> get_this_week_all_task();
        if($resDone){
            foreach($resDone as $k => $v){
                $task_id = $resDone[$k]['task_id'];
                $resTask = D('Task') -> get_task_by_id($task_id);
                $resDone[$k]['detail'] = $resTask['detail'];
            }
            if(empty($type)){
                return $resDone;
            }else {
                foreach ($resDone as $key => $val) {
                    if ($val['type'] == $type) {
                        $result[$key] = $val;
                    }
                }
                return $result;
            }
        }else{
            return false;
        }
    }


    /**
     * 二维数组转一维数组
     * @param $input
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    public function i_array_column($input, $columnKey, $indexKey=null){
        if(!function_exists('array_column')){
            $columnKeyIsNumber  = (is_numeric($columnKey))  ?true :false;
            $indexKeyIsNull     = (is_null($indexKey))      ?true :false;
            $indexKeyIsNumber   = (is_numeric($indexKey))   ?true :false;
            $result             = array();
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

    /**
     * 获取数组中键值名相同的个数
     * @param $arr  array   数组
     * @param $key  string  键名
     * @param $value        键值
     * @return int
     */
    public function get_count($arr,$key,$value){
        foreach ($arr as $k => $v) {
            if ($v[$key] == $value) {
                $num[$k] = $v;
                $re = count($num);
            }
        }
        return $re = $re == 0 ? 0 : $re;
    }

}
