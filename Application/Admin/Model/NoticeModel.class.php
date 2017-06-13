<?php

namespace Admin\Model;
use Think\Model;

class NoticeModel extends Model {
    protected $_validate = array(
        array('notice_title', 'require', '公告主题不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('notice_type', 'require', '公告类型不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('content', 'require', '公告内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    public function notice_insert($data){
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

    public function notice_find($where=null){
        $re = $this -> where('id = '.$where) -> find();
//        $re = $this -> where($where) -> find();
        if($re){
            return true;
        }else{
            return false;
        }
    }

}
