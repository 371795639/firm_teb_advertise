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

    /*
     * 插入数据
     * @param  array    $data 数据
     * @return boolean  ture-插入数据成功，false-插入数据失败
     */
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

    /*
      * 根据ID查找推广专员
      * @param  integer  $staff_id   推广专员ID
      * @return array    $re         查找的数据
      */
    public function msg_find($staff_id){
        $re = $this -> where(array('id'=>(int)$staff_id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
      * 根据ID更新推广专员的信息
      * @param  integer  $staff_id   推广专员ID
      * @param  array    $data       要更新的数据
      * @return array    $re         更新结果
      */
    public function msg_save($staff_id,$data){
        $re = $this -> where(array('id' => (int)$staff_id)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取推广专员的信息
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return array    $re     搜索结果
     */
    public function get_all_msg($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /**
     * 获取指定字段下的值
     * @param $field
     * @return bool|mixed
     */
    public function getCash($field){
        $re = $this->field($field)->select();
        $re['game_num'] = 0;
        $re['over_plus'] = 0;
        if(!empty($re)){
            foreach ($re as $value){
                $re['over_plus'] += $value['money'];
                $re['game_num'] += $value['consume_coin'];
            }
        }
        return $re;
    }

}
