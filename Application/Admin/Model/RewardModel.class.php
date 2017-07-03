<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/22
 * Time: 15:43
 */
namespace Admin\Model;
use Think\Model;

class RewardModel extends Model{

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
    public function getBonus($result){
        foreach ($result as $key=>$val){
            $result[$key]['number'] = $key + 1;
            $user_ids[] = $val['uid'];
        }
        $user_messages = D('staff')->where(array('id'=>array('in',$user_ids)))->select();
        $user_info = M('staff_info')->where(array('uid'=>array('in',$user_ids)))->select();
        if(!empty($user_messages) && !empty($user_info)){
            foreach ($result as $key=>$value){
                foreach ($user_messages as $item){
                    if($item['id'] == $value['uid']){
                        $result[$key]['real_name'] = $item['staff_real'];
                        $result[$key]['mobile'] = $item['mobile'];
                    }
                }
                foreach ($user_info as $items){
                    if($items['uid'] == $value['uid']){
                        $result[$key]['credit_value'] = $items['credit_value'];
                        $result[$key]['fix_bonus'] = $items['fix_bonus'];
                        $result[$key]['extra_bonus'] = $items['extra_bonus'];
                    }
                }
            }
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取所有的奖励记录（除去分红奖励）
     * @param $result
     * @return bool
     */
    public function getAllBonus($result){
        foreach ($result as $key=>$val){
            $result[$key]['number'] = $key + 1;
            $user_ids[] = $val['uid'];
        }
        $user_messages = D('staff')->where(array('id'=>array('in',$user_ids)))->select();
        if(!empty($user_messages)){
            foreach ($result as $key=>$value){
                if($value['type'] == 3 || $value['type'] == 4){
                    $result[$key]['money'] = $value['base_money'];
                }else{
                    $result[$key]['money'] = $value['extra_money'];
                }
                if($value['type'] == 2){
                    $result[$key]['value'] = 2;
                    $result[$key]['type'] = '任务奖励';
                }
                if($value['type'] == 3){
                    $result[$key]['value'] = 3;
                    $result[$key]['type'] = '推荐奖励';
                }
                if($value['type'] == 4){
                    $result[$key]['value'] = 4;
                    $result[$key]['type'] = '充值提成';
                }
                if($value['type'] == 5){
                    $result[$key]['value'] = 5;
                    $result[$key]['type'] = '中心推荐奖励';
                }
                if($value['type'] == 6){
                    $result[$key]['value'] = 6;
                    $result[$key]['type'] = '中心业绩奖励';
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

    public function getCash(){
        $result = $this->field('type,base_money,extra_money')->select();
        $re['bonus_money'] = 0;
        $re['task_money'] = 0;
        $re['recharge_money'] = 0;
        $re['recommend_money'] = 0;
        $re['re_concert_money'] = 0;
        $re['re_recharge_money'] = 0;
        if(!empty($result)){
            foreach ($result as $value){
                if($value['type'] == 1){//分红
                    $re['bonus_money'] += $value['extra_money'];
                }
                if($value['type'] == 2){//任务
                    $re['task_money'] += $value['extra_money'];
                }
                if($value['type'] == 3){//推荐
                    $re['recommend_money'] += $value['base_money'];
                }
                if($value['type'] == 4){//充值
                    $re['recharge_money'] += $value['extra_money'];
                }
                if($value['type'] == 5){//中心推荐
                    $re['re_concert_money'] += $value['extra_money'];
                }
                if($value['type'] == 6){//中心充值业绩
                    $re['re_recharge_money'] += $value['extra_money'];
                }
            }
        }
        return $re;
    }
}