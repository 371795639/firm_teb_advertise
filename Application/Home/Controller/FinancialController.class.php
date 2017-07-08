<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class FinancialController extends HomeController {

    /* 报表 */
    public function financialStatements(){
        $this->display();
    }

    /* 报表本周信息返回 */
    public function financialThisWeekReturn(){
        $dbCharge   = M('charge');
        $uid = $_SESSION['userid'];

        //获取本周周一到周日的日期
        $sdefaultDate = date("Y-m-d");
        $first=1;
        $w=date('w',strtotime($sdefaultDate));
        $week_start=date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
        for($i=0;$i<7;$i++){
            $time[$i] = date('n-d',strtotime("$week_start +$i days"));
            $timeStart[$i] = date('Y-m-d 00:00:00',strtotime("$week_start +$i days")); //一天最小值
            $timeEnd  [$i] = date('Y-m-d 23:59:59',strtotime("$week_start +$i days"));   //一天最大值
        }
        //获取本周周一到周日的充值记录
        $map['create_time'] = array(array('EGT',$timeStart[0]),array('ELT',$timeEnd[0]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[0] = $dbCharge->where($map)->Sum('money'); //本周一
        $map['create_time'] = array(array('EGT',$timeStart[1]),array('ELT',$timeEnd[1]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[1] = $dbCharge->where($map)->Sum('money'); //本周二
        $map['create_time'] = array(array('EGT',$timeStart[2]),array('ELT',$timeEnd[2]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[2] = $dbCharge->where($map)->Sum('money'); //本周三
        $map['create_time'] = array(array('EGT',$timeStart[3]),array('ELT',$timeEnd[3]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[3] = $dbCharge->where($map)->Sum('money'); //本周四
        $map['create_time'] = array(array('EGT',$timeStart[4]),array('ELT',$timeEnd[4]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[4] = $dbCharge->where($map)->Sum('money'); //本周五
        $map['create_time'] = array(array('EGT',$timeStart[5]),array('ELT',$timeEnd[5]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[5] = $dbCharge->where($map)->Sum('money'); //本周六
        $map['create_time'] = array(array('EGT',$timeStart[6]),array('ELT',$timeEnd[6]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[6] = $dbCharge->where($map)->Sum('money'); //本周日
        //充值金额为NULL的转为0
        for($i=0 ; $i<7 ;$i++ ){
            if(!$refDbCharge[$i]){
                $refDbCharge[$i] = 0;
            }
        }
        $data = array(
            'categories'=>array(
                '0' => $time[0], //第一下标必须是0
                '1' => $time[1],
                '2' => $time[2],
                '3' => $time[3],
                '4' => $time[4],
                '5' => $time[5],
                '6' => $time[6],
            ),//日期
            'data'=>array(
                '0' => $refDbCharge[0], //第一下标必须是0
                '1' => $refDbCharge[1],
                '2' => $refDbCharge[2],
                '3' => $refDbCharge[3],
                '4' => $refDbCharge[4],
                '5' => $refDbCharge[5],
                '6' => $refDbCharge[6],
            )//充值
        );
        $this->ajaxReturn($data);
    }

    /* 报表上周信息返回 */
    public function financialLastWeekReturn(){
        $dbCharge   = M('charge');
        $uid = $_SESSION['userid'];

        //获取上周周一的日期
        $beginLastweek  = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $last_week_start= date("Y-m-d H:i:s",$beginLastweek );
        for($i=0;$i<7;$i++){
            $time[$i] = date('n-d',strtotime("$last_week_start +$i days"));//$time 存储的是上周一到上周日的所有日期
            $timeStart[$i] = date('Y-m-d 00:00:00',strtotime("$last_week_start +$i days"));//$timeStart存储的上周一到周日每一天最小值
            $timeEnd  [$i] = date('Y-m-d 23:59:59',strtotime("$last_week_start +$i days"));//$timeEnd存储的上周一到周日每一天最大值
        }
        //获取上周周一到周日的充值记录
        $map['create_time'] = array(array('EGT',$timeStart[0]),array('ELT',$timeEnd[0]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[0] = $dbCharge->where($map)->Sum('money'); //本周一
        $map['create_time'] = array(array('EGT',$timeStart[1]),array('ELT',$timeEnd[1]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[1] = $dbCharge->where($map)->Sum('money'); //本周二
        $map['create_time'] = array(array('EGT',$timeStart[2]),array('ELT',$timeEnd[2]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[2] = $dbCharge->where($map)->Sum('money'); //本周三
        $map['create_time'] = array(array('EGT',$timeStart[3]),array('ELT',$timeEnd[3]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[3] = $dbCharge->where($map)->Sum('money'); //本周四
        $map['create_time'] = array(array('EGT',$timeStart[4]),array('ELT',$timeEnd[4]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[4] = $dbCharge->where($map)->Sum('money'); //本周五
        $map['create_time'] = array(array('EGT',$timeStart[5]),array('ELT',$timeEnd[5]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[5] = $dbCharge->where($map)->Sum('money'); //本周六
        $map['create_time'] = array(array('EGT',$timeStart[6]),array('ELT',$timeEnd[6]),'and');
        $map['pay_id'] = array('eq',$uid);
        $refDbCharge[6] = $dbCharge->where($map)->Sum('money'); //本周日
        //充值金额为NULL的转为0
        for($i=0 ; $i<7 ;$i++ ){
            if(!$refDbCharge[$i]){
                $refDbCharge[$i] = 0;
            }
        }
        $data = array(
            'categories'=>array(
                '0' => $time[0], //第一下标必须是0
                '1' => $time[1],
                '2' => $time[2],
                '3' => $time[3],
                '4' => $time[4],
                '5' => $time[5],
                '6' => $time[6],
            ),//日期
            'data'=>array(
                '0' => $refDbCharge[0], //第一下标必须是0
                '1' => $refDbCharge[1],
                '2' => $refDbCharge[2],
                '3' => $refDbCharge[3],
                '4' => $refDbCharge[4],
                '5' => $refDbCharge[5],
                '6' => $refDbCharge[6],
            )//充值
        );
        $this->ajaxReturn($data,"JSON");
    }

}
