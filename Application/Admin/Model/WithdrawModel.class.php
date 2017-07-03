<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/24
 * Time: 11:52
 */
namespace Admin\Model;
use Think\Model;
class WithdrawModel extends Model{

    /**
     * 获得所有的信息
     * @return bool|mixed
     */
    public function getMessage(){
        $result = $this->select();
        if($result){
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 数据结果集重新组合
     * @param $result
     * @return bool|mixed
     */
    public function getCash($result){
        foreach ($result as $key=>$val){
            $result[$key]['number'] = $key + 1;
            $user_ids[] = $val['uid'];
        }
        $user_messages = D('staff')->where(array('id'=>array('in',$user_ids)))->select();
        if(!empty($user_messages)){
            foreach ($result as $key=>$value){
                if($value['status'] == 1){
                    $result[$key]['statue'] = '待提现';
                }
                if($value['status'] == 2){
                    $result[$key]['statue'] = '已提现';
                }
                foreach ($user_messages as $item){
                    if($item['id'] == $value['uid']){
                        $result[$key]['real_name'] = $item['staff_real'];
                        $result[$key]['mobile'] = $item['mobile'];
                    }
                }
            }
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 更新数据
     * @param $condition
     * @param $data
     * @return bool
     */
    public function updateData($condition,$data){
        $result = $this->where($condition)->save($data);
        if($result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取指定字段下的值
     * @param $field
     * @return bool|mixed
     */
    public function getMoney($field){
        $re = $this->field($field)->select();
        if($re){
            $res = array_sum($re) + 0;
            return $res;
        }else{
            return false;
        }
    }
}