<?php

namespace Admin\Model;
use Think\Model;

class NoticeModel extends Model {
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
    * @param  array    $data       要插入的数据
    * @return boolean  ture-插入数据成功，false-插入数据失败
    */
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

    /*
     * 根据ID查找消息
     * @param  integer  $notice_id  消息ID
     * @return array    $re         消息列表
     */
    public function get_notice_by_id($notice_id){
        $re = $this -> where(array('id'=>(int)$notice_id)) -> find();
        if($re){
           return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据ID插入数据
     * @param  integer  $notice_id  消息ID
     * @param  array    $data       要更新的数据
     * @return array    $re         更新结果
     */
    public function save_notice_by_id($notice_id,$data){
        $re = $this -> where(array('id'=>(int)$notice_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
    * 根据条件获取消息
    * @param   string  $field  字段组成的字符串
    * @param   array   $map    搜索条件
    * @return array    $re     搜索结果
    */
    public function get_all_notice($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


}
