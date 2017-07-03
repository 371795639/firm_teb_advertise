<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/24
 * Time: 16:52
 */
namespace Admin\Model;
use Think\Model;

class ChargeModel extends Model{

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
    public function getCharge($result){
        foreach ($result as $key=>$val){
            $result[$key]['number'] = $key + 1;
            $user_ids[] = $val['uid'];
        }
        $user_messages = D('staff')->where(array('id'=>array('in',$user_ids)))->select();
        if(!empty($user_messages)){
            foreach ($result as $key=>$value){
                if($value['type'] == 1){
                    $result[$key]['val'] = '自我充值';
                }
                if($value['type'] == 2){
                    $result[$key]['val'] = '为他人充值';
                }
                foreach ($user_messages as $item){
                    if($item['id'] == $value['uid']){
                        $result[$key]['real_name'] = $item['staff_real'];
//                        $result[$key]['mobile'] = $item['mobile'];
                    }
                }
            }
            return $result;
        }else{
            return false;
        }
    }

}