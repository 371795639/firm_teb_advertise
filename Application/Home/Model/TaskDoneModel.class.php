<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class TaskDoneModel extends Model{

    /**
     * 根据字段和值，查找已完成任务
     * @param $key          string    字段
     * @param $value        integer    值
     * @param null $what    string     find：查询一条；select：查询所有
     * @return bool|mixed|null
     */
    public function get_done_by_uid($key,$value,$what=null){
        $where['uid'] = $_SESSION['userid'];
        if($what == 'find'){
            $re = $this -> where(array($key => $value)) -> where($where) -> find();
        }elseif($what == 'select') {
            $re = $this -> where(array($key => $value)) -> where($where)-> select();
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
     * 插入数据
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
     * @param $date     string    当前时间
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
     * @param $date     string    当前时间
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
     * 根据当前时间获取这周一的某个时间
     * @param $date     string  日期
     * @param $format   string  时间
     * @return bool|string
     */
    public function get_monday_time($date,$format){
        if(!empty($date)) {
            $time = strtotime($date);
            $week = date('N', $time);
            if ($week == 1) {
                $start_time = date('Y-m-d '.$format, strtotime('monday', $time));       //'Y-m-d 'd后要保留空格，否则时间日和小时之间没有空格
            } else {
                $start_time = date('Y-m-d '.$format, strtotime('-1 monday', $time));
            }
        }else{
            $start_time = "";
        }
        return $start_time;
    }


    /**
     * 根据用户ID获取本周内所有已领取的任务
     * @param   $group  string      根据字段进行分组
     * @param   $uid    integer     根据$uid获取本周任务；传入空，则返回所有
     * @return  mixed
     */
//    public function get_this_week_all_task($uid,$group){
    public function get_all_task($uid,$group,$taskId){
        if($uid){
            $map['uid'] = $uid;
        }
        if($taskId){
            $map['task_id'] = array('neq',0);
        }
        if(empty($group)){
            $res = $this -> where($map) -> select();//有值就说明已经领取过了
        }else{
            $res = $this -> where($map) -> group($group) -> select();//有值就说明已经领取过了
        }
        foreach($res as $k => $v){
            $task_id    = $res[$k]['task_id'];
            $task_ids   = D('Task') -> get_task_by_id($task_id);
            $res[$k]['type']    = $task_ids['type'];
            $res[$k]['money']   = $task_ids['money'];
            $res[$k]['inneed']  = $task_ids['inneed'];
            $res[$k]['name']    = $task_ids['name'];
            $res[$k]['detail']  = $task_ids['detail'];
        }
        return $res;
    }


    /**
     * 根据用户ID获取本周内已领取的任务（不包含结算数据）
     * @param   $group  string      根据字段进行分组
     * @param   $uid    integer     根据$uid获取本周任务；传入空，则返回所有
     * @param   $status integer     任务的状态
     * @param   $limit  integer     取出来的限制条数
     * @return  mixed
     */
//    public function get_this_week_doing_task($uid,$status,$group,$limit){
    public function get_doing_task($uid,$status,$group,$limit){
        $map = array(
            'task_id'       => array('gt',0),
        );
        if(is_string($status)){
            $map['status']  = array('in',$status);
        }else{
            $map['status']  = $status;
        }
        if($uid){
            $map['uid']     = $uid;
        }
        if(empty($group)){
            $res = $this -> where($map) -> order('id DESC')-> limit($limit) -> select();
        }else{
            $res = $this -> where($map) -> group($group) -> order('id DESC')-> limit($limit) -> select();
        }
        foreach($res as $k => $v){
            $task_id    = $res[$k]['task_id'];
            $task_ids   = D('Task') -> get_task_by_id($task_id);
            $res[$k]['type']    = $task_ids['type'];
            $res[$k]['money']   = $task_ids['money'];
            $res[$k]['inneed']  = $task_ids['inneed'];
            $res[$k]['name']    = $task_ids['name'];
            $res[$k]['detail']  = $task_ids['detail'];
        }
        return $res;
    }


    /**
     * 获取结算数据
     * @param $uid      integer     用户ID
     * @param $field    string      字段
     * @param $what     string      返回方式
     * @return array|false|mixed
     */
    public function get_task_field($uid,$field,$what){
        $map = array(
            'task_id'   => 0,
            'status'    => 8,
        );
        if($uid){
            $map['uid'] = $uid;
        }
        switch (strtolower($what)){
            case 'select':
                $res = $this -> where($map) -> find();
                break;
            case 'field':
                $res = $this -> where($map) -> getField($field);
                break;
            default :
                $res = '参数错误';
        }
        return $res;
    }


    /**
     * 在task_done表中根据get_time和uid获取任务类型
     * @param   $uid    string      根据$uid获取本周任务,传入空，则返回所有
     * @param   $group  string      根据字段进行分组
     * @param   $type   integer     任务类型 1：日常任务 2：额外任务
     * @return  bool
     */
//    public function get_this_week_task($uid,$group,$type,$taskId){
    public function get_week_type_task($uid,$group,$type,$taskId){
        $resDone = $this -> get_all_task($uid,$group,$taskId);
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
            $columnKeyIsNumber  = (is_numeric($columnKey))  ? true : false;
            $indexKeyIsNull     = (is_null($indexKey))      ? true : false;
            $indexKeyIsNumber   = (is_numeric($indexKey))   ? true : false;
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
     * @param $arr      array   数组
     * @param $key      string  键名
     * @param $value    string  键值
     * @return int      integer 数量
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


    /**根据$key，$value更新已完成任务信息
     * @param $key      string  字段名
     * @param $value    string  字段值
     * @param $data     array   要更新的数据
     * @return bool     true：更新成功；false：更新失败
     */
    public function save_done($key,$value,$data){
        $re = $this -> where(array($key => $value)) -> save($data);
        if($re){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 获取上周用户领取所有的任务
     * @param  $uid     integer 根据$uid获取本周任务,传入空，则返回所有
     * @param $date     string  日期
     * @return mixed    array   任务列表
     */
    public function get_last_week_done($date,$uid){
        $monday = get_last_monday($date);
        $sunday = get_last_sunday($date);
        $map    = array(
            'get_time'  => array(array('gt',$monday),array('lt',$sunday)),
            'task_id'   => array('gt',0),
            'get_money' => 1,
        );
        if($uid){
            $map['uid'] = $uid;
            $res = $this -> where($map) -> select();
        }else{
            $res = $this -> where($map) -> select();
        }
        foreach($res as $k => $v){
            $task_id    = $res[$k]['task_id'];
            $task_ids   = D('Task') -> get_task_by_id($task_id);
            $res[$k]['type']    = $task_ids['type'];
            $res[$k]['money']   = $task_ids['money'];
            $res[$k]['inneed']  = $task_ids['inneed'];
            $res[$k]['name']    = $task_ids['name'];
            $res[$k]['detail']  = $task_ids['detail'];
        }
        return $res;

    }


    /**
     * 在task_done表中根据get_time和uid获取任务类型
     * @param   $uid  integer 根据$uid获取本周任务,传入空，则返回所有
     * @param   $date string  日期
     * @param   $type integer 任务类型 1：日常任务 2：额外任务
     * @return  bool
     */
    public function get_last_week_task($date,$uid,$type){
        $resDone = $this -> get_last_week_done($date,$uid);
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
     * 根据$date获取上周内已完成的日常任务列表/uid
     * @param $date string  日期
     * @param $uids integer 根据$uid获取本周任务,传入空，则返回所有
     * @param $what string  uid:返回去重的uid数组；select：返回$re
     * @return mixed
     */
    public function get_time_in_last_week($date,$uids,$what){
        $re = $this -> get_last_week_done($date,$uids);
        //获取日常任务
        foreach ($re as $key => $val) {
            if ($val['type'] == 1) {
                $res[$key] = $val;
            }
        }
        /*Begin the core codes for outputting uids*/
        foreach($res as $k){
            $new['uid'] = $k['uid'];
            $new['status'] = $k['status'];
            $newIds[] = $new;
        }
        $arr_status = [];
        foreach($newIds as $k){
            $k['status'] == 2 && $arr_status[$k['uid']] += 1;
        }
        $uid_list = [];
        foreach($arr_status as $uid => $qty){
            $qty  >=4 && $uid_list[] = $uid;  //TODO：$qty>= 的值 为日常任务总数
        }
        /**End**/
        if(empty($res)){
            return false;
        }else {
            switch ($what) {
                case 'uid':
                    return $uid_list;
                    break;
                case 'select':
                    return $res;
                    break;
                default:
                    return '参数错误';
            }
        }
    }


    /**
     * 获取上周用户领取所有的任务
     * @param $uid      integer 根据$uid获取本周任务,传入空，则返回所有
     * @param $date     string  日期
     * @param $group    string  分组字段
     * @param $what     string  uid:返回去重的uid数组；select：返回$re
     * @return mixed    array   任务列表
     */
    public function get_last_week_done_group($date,$uid,$group,$what){
        $monday = get_last_monday($date);
        $sunday = get_last_sunday($date);
        $map = array(
            'get_time'  => array(array('gt',$monday),array('lt',$sunday)),
            'task_id'   => array('gt',0),
        );
        if($uid && empty($group)){
            $map['uid'] = $uid;
            $res = $this -> where($map) -> select();
        }else{
            $res = $this -> where($map) -> group($group) -> select();
        }
        foreach($res as $k => $v){
            $task_id    = $res[$k]['task_id'];
            $task_ids   = D('Task') -> get_task_by_id($task_id);
            $res[$k]['type']    = $task_ids['type'];
            $res[$k]['money']   = $task_ids['money'];
            $res[$k]['inneed']  = $task_ids['inneed'];
            $res[$k]['name']    = $task_ids['name'];
            $res[$k]['detail']  = $task_ids['detail'];
        }
        switch($what){
            case 'select':
                return $res;
            break;
            case 'uids':
                foreach($res as $k => $v){
                    $uidAll[]   = $res[$k]['uid'];
                }
                return $uidAll;
            break;
            default :
                return '参数错误';
        }
    }


    /**
     * 获取用户上周领取的某一任务指标
     * @param $date     string  时间
     * @param $uid      integer 用户ID
     * @param $names    string  任务名称
     * @return mixed    integer 任务指标
     */
    public function get_task_inneed($date,$uid,$names){
        $re = $this -> get_last_week_done($date,$uid);
        foreach ($re as $key => $val) {
            if ($val['name'] == $names) {
                $res[$key] = $val;
            }
        }
        foreach($res as $k => $v){
            return $res[$k]['inneed'];
        }
    }
}
