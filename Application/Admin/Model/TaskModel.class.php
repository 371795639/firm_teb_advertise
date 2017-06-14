<?php

namespace Admin\Model;
use Think\Model;

class TaskModel extends Model {
    protected $_validate = array(
        array('name', 'require', '任务名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('inneed', 'require', '任务指标不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('start_time', 'require', '开始时间不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('end_time', 'require', '结束时间不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    public function task_insert($data){
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

}
