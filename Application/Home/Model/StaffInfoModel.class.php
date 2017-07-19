<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class StaffInfoModel extends Model{
    /*
     * 根据ID查找推广专员
     * @param  integer  $uid    推广专员ID
     * @return array    $re     查找的数据
     */
    public function get_staff_by_uid($uid){
        $re = $this -> where(array('uid'=>(int)$uid)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据ID更新数据
     * @param  integer  $uid    推广专员ID
     * @param  array    $data   要更新的数据
     * @return array    $re     插入的数据
     */
    public function save_staff_by_uid($uid,$data){
        $re = $this -> where(array('uid'=>(int)$uid)) -> save($data);
        if($re){
            return $re;
        }else{
            return false;
        }
    }

    /*
     * 根据条件获取推广专员列表
     * @param   string  $field  字段组成的字符串
     * @param   array   $map    搜索条件
     * @return  array   $re     推广专员列表
     */
    public function get_all_task($field,$map){
        $re = $this -> Field($field) -> where($map) -> order('id DESC') -> select();
        if($re){
            return $re;
        }else{
            return false;
        }
    }


    /**根据完成任务次数返回信用值
     * @param $num  integer 完成游戏次数
     * @return int  integer 信用值
     */
    public function get_credit($num){
        $num = intval($num);
        $num = $num >= 4 ? 4 : $num;
        $num = $num <= -5 ? -5 : $num;
        switch($num){
            case -5:
                $credit = 10;
                break;
            case -4:
                $credit = 35;
                break;
            case -3:
                $credit = 65;
                break;
            case -2:
                $credit = 85;
                break;
            case -1:
                $credit = 95;
                break;
            case 0:
                $credit = 100;
                break;
            case 1:
                $credit = 105;
                break;
            case 2:
                $credit = 115;
                break;
            case 3:
                $credit = 135;
                break;
            case 4:
                $credit = 150;
                break;
        }
        return $credit;
    }

}
