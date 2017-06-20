<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Think\Controller;

class CashController extends AdminController {

    /**提取起止日期 表中字段是create_time**/
    public function _queryCreateTime(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        if($start_time || $end_time){
            if($start_time >= $end_time){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['create_time'] = array(array('gt', $start_time), array('lt', $end_time));
            }
        }
        return $map;
    }

    /**提取起止日期 表中字段是post_time**/
    public function _queryPostTime(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        if($start_time || $end_time){
            if($start_time >= $end_time){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['post_time'] = array(array('gt', $start_time), array('lt', $end_time));
            }
        }
        return $map;
    }


    /**分红管理**/
    public function cashGiven(){

        $this -> meta_title = '分红管理';
        $this -> display('Main/cash/cashGiven');
    }


    /**出入帐理**/
    public function cashIo(){

        $this -> meta_title = '出入帐理';
        $this -> display('Main/cash/cashIo');
    }


    /**财务总表**/
    public function cashTotal(){

        $this -> meta_title = '财务总表';
        $this -> display('Main/cash/cashTotal');
    }


    /**奖励明细**/
    public function cashDetail(){

        $this -> meta_title = '奖励明细';
        $this -> display('Main/cash/cashDetail');
    }

// 流水表  提现表  充值表

























}
