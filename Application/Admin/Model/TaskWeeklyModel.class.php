<?php

namespace Admin\Model;
use Think\Model;

class TaskWeeklyModel extends Model {
    protected $_validate = array(
        array('name', 'require', '任务名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('inneed', 'require', '任务指标不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('money', 'require', '任务金额不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('create_time', DATE, self::MODEL_INSERT,'function'),
    );

    /*
     * 插入数据
     * @param  array    $data 数据
     * @return boolean  ture-插入数据成功，false-插入数据失败
     */
    public function weekly_insert($data){
        if($this -> create($data)){
           $re = $this -> add();
            if($re){
                return true;
            }else{
                $this -> error ='';
                return false;
            }
        }else{
            return false;
        }
    }

    /*
     * 根据ID查找任务
     * @param  integer  $id     周任务的ID
     * @return array    $re     查找的数据
     */
    public function get_weekly_by_id($id){
        $re = $this -> where(array('id'=>(int)$id)) -> find();
        if($re){
           return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据task_id更新数据
     * @param  integer  $task_id    任务ID
     * @param  array    $data       要更新的数据
     * @return array    $re         插入的数据
     */
    public function save_weekly_by_id($task_id,$data){
        $re = $this -> where(array('task_id'=>(int)$task_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取任务列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     任务列表
     */
    public function get_all_weekly($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据周任务的ID删除周列表
     * @param   array   $id    周任务ID
     * @return  array   $re
     */
    public function delete_weekly_by_id($id){
        $re = $this -> where(array('id'=>(int)$id)) -> delete();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

























}
