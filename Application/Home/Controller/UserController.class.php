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
class UserController extends HomeController {

    /* 完善用户信息 */
    public function compeleInfo(){
        if(IS_POST){
            //解决中文乱码
            header('Content-Type:text/html;charset=utf-8');
            $dbStaff = D('staff');
            $userid  = $_SESSION['userid'];
            $refData = array(
                'game_id'     => $_POST['gameId'] ,
                'card_id'     => $_POST['cardNum'],
                'address'     => $_POST['address'],
            );
            //判断是否存在该推荐人以及该手机号是否匹配
            $refStaffExist = $dbStaff->where(array('staff_real' => $_POST['staffName'] ,'mobile' => $_POST['refPhoneNum']))->find();
            if($refStaffExist){
                if($refStaffExist['id']==$userid){
                    $this->error('推荐人不能是自己',U('User/compeleInfo'));
                }
                else{
                    $refData['referee'] = $refStaffExist['id'];
                    $ref = $dbStaff->where('id='.$userid)->save($refData);
                    if($ref){
                        $this->assign('waitSecond','3');
                        $this->success('完善信息成功，正在跳转到个人页面', U('User/index'));exit();
                    }
                }
            }
           else{
                $this->error('推荐人和手机号不匹配！', U('User/compeleInfo'));
           }
        }
            $this->display();
    }

    /* 主页面 */
    public function index(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();
        $this->assign('resStaff',$resStaff);
        /*显示剩余任务个数--开始*/
        $dbTaskDone     = D('TaskDone');
        $resDoneCount   = $dbTaskDone -> get_this_week_all_task();
        $doingNo        = $dbTaskDone -> get_count($resDoneCount,'status',1);
        $this->assign('doingNo',$doingNo);
        $time = date('H:i:s');
        if($time <= "12:00:00"){
            $pic = 1;
        }else{
            $pic = 2;
        }
        $this->assign('pic',$pic);
        /*显示剩余任务个数--结束*/
        $this->display();
    }

	/* 我的页面 */
	public function my(){
        $dbStaff        = D('staff');
        $dbStaffInfo    = D('StaffInfo');
        $resStaff       = $dbStaff      -> get_staff_by_id($_SESSION['userid']);
        $resStaffInfo   = $dbStaffInfo  -> get_staff_by_uid($_SESSION['userid']);
        $class          = $resStaffInfo['class'];
        if($resStaff['is_league'] = 0){
            $re = '推广专员';
        }else{
            $res = '级加盟商';
            switch($class){
                case 1:
                    $re = '一'.$res;
                    break;
                case 2:
                    $re = '二'.$res;
                    break;
                case 3:
                    $re = '三'.$res;
                    break;
            }
        }
        $this->assign('re',$re);
        $this->assign('resStaff',$resStaff);
	    $this->display();
	}

    /* 奖励中心 */
    public function encourage(){
        $dbReward  = M('reward');
        $userid    = $_SESSION['userid'];
        //类型 1：分红、2：任务奖励、3：推荐奖励、4：充值提成；5中心推荐奖励；6中心业绩奖励;7:分销奖励
        //游戏分红
        $bonusReward = $dbReward->where(array('uid' => $userid , 'type' => 8))->order('create_time desc')->select();
        //任务奖励
        $taskReward = $dbReward->where(array('uid' => $userid , 'type' => array('in','1,2')))->order('create_time desc')->select();
        //推荐奖励
        $map['uid']  = array('EQ',$userid);
        $map['type'] = array('EGT',3);
        $spreadReward = $dbReward->where($map)->order('create_time desc')->select();
        //输出模板
        $this->assign('bonusReward',$bonusReward);
        $this->assign('taskReward',$taskReward);
        $this->assign('spreadReward',$spreadReward);
        $this->display();
    }

    /* 财务管理 */
    public function rechargeWithdrawCash(){
        $dbwithdraw = M('withdraw');
        $dbcharge   = M('charge');
        $uid = $_SESSION['userid'];
        $currMinTime = date('Y-m-01 00:00:00',time());          //获取当前月份最小时间
        //本月提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且大于等于当前月份最小时间
        $condition['status'] = array('eq',2 );                  //已提现
        $condition['create_time'] = array('EGT',$currMinTime);
        $preMonthWithdraw = $dbwithdraw->where($condition)->order('create_time desc')->select();
        //历史提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且小于当前月份最小时间
        $condition['status'] = array('eq',2 );                  //已提现
        $condition['create_time'] = array('LT',$currMinTime);
        $hisMonthWithdraw = $dbwithdraw->where($condition)->order('create_time desc')->select();
        //本月充值
        $condition['pay_id'] = array('eq',$uid );               //等于pay_id且大于等于当前月份最小时间
        $condition['create_time'] = array('EGT',$currMinTime);
        $preMonthCharge = $dbcharge->where($condition)->order('create_time desc')->select();
        //历史充值
        $condition['uid'] = array('eq',$uid );                  //等于uid且小于当前月份最小时间
        $condition['create_time'] = array('LT',$currMinTime);
        $hisMonthCharge = $dbcharge->where($condition)->order('create_time desc')->select();
        //渲染
        $this->assign('refWidthdraw',$preMonthWithdraw);
        $this->assign('hisMonthWithdraw',$hisMonthWithdraw);
        $this->assign('preMonthCharge',$preMonthCharge );
        $this->assign('hisMonthCharge',$hisMonthCharge );
        $this->display();
    }

    /* 推广管理 */
    public function spreadManage(){
//        $uid   = $_SESSION['userid'];
//        //$staffAllCount = $dbStaff->where(1)->count();             //查询表的总记录数
//
//
//        var_dump($this->fen($uid)) ; die();
//        //$this->assign('spreadCooperate', $staffSub);
//        $this->display();
    }
//
//    private function fen($uid){
//        $dbStaff     = M('staff');
//        $staffAll    = $dbStaff->where("id = $uid")->select();                  //查询表的记录详情
//        global $str;
//        foreach($staffAll as $key=>$val)
//        {
//            if($staffAll['referee']!=NULL){
//                $str.=$staffAll['staff_name']."<br>";//拼接改用户的昵称
//                $this->fen($val['id']);
//            }
//
//        }
//        return $str;
//    }



}
