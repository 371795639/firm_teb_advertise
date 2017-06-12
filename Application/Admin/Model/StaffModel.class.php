<?php

namespace Admin\Model;
use Think\Model;

class StaffModel extends Model {
    protected $_validate = array(
        array('staff_name', 'require', '昵称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('referee', 'require', '推荐人ID不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', '1', self::MODEL_INSERT),
    );

    public function msg_insert($data){
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

    public function msg_find($where=null){
        $re = $this -> where('id = '.$where) -> find();
//        $re = $this -> where($where) -> find();
        if($re){
            return true;
        }else{
            return false;
        }
    }

}
