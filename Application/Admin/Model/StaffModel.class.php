<?php

namespace Admin\Model;
use Think\Model;

class StaffModel extends Model {
    protected $_validate = array(
        array('staff_real', 'require', '推广专员的真实姓名不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('mobile', 'require', '手机号不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('game_id', 'require', '推广专员的游戏ID不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('address', 'require', '推广专员的地址', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('status','1'),
        array('pay_status','3'),
        array('create_time',DATE,3,'function'),
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
        $re = $this -> where(array('id'=>$staff_id)) -> find();
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
        $re = $this -> where(array('id' => $staff_id)) -> save($data);
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


    /**
     * 获取所有推荐人是同一人的推广专员
     * @param $referee  integer 推荐人ID
     * @param $what     string  find：返回一条数据；select：返回所有信息；count：反馈数量
     * @return bool|int|mixed|string
     */
    public function get_staff_by_referee($referee,$what){
        $map = array(
            'referee'   => $referee,
            'is_league' => 0,
        );
        switch($what){
            case 'find':
                $re = $this -> where($map) -> find();
                break;
            case 'select':
                $re = $this -> where($map) -> select();
                break;
            case 'count':
                $res = $this -> where($map) -> select();
                $re = count($res) == 0 ? 0 : count($res);
                break;
            default:
                $re = '参数错误';
        }
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /** 查找推广专员
     * @param $key  string  字段名
     * @param $val  string  字段值
     * @return array|bool
     */
    public function get_staff($key,$val){
        $re = $this -> where(array($key=>$val)) -> find();
        if(empty($re)){
            return false;
        }else{
            return $re;
        }
    }


    /**
     * 推荐人推荐金额补差价
     * @param $count    integer     推荐人数量
     * @return int      应补金额
     */
    public function referee_given($count){
        $money = 0;
        if($count > 0){
            if($count == 1){
                $money = 1.2*1000;
            }elseif($count == 2){
                $money = (1.5-1.2)*1000;
            }elseif($count == 3){
                $money = (3-1.5)*1000;
            }elseif($count >= 4){
                $money = 1*1000;
            }
        }else{
            $money = 0;
        }
        return $money;
    }
}
