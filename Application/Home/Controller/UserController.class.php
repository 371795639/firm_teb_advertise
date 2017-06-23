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
            $userid = $_SESSION['userid'];
            $refData = array(
                'game_id'     => $_POST['gameId'],
                'card_id'     => $_POST['cardNum'],
            );
            //判断是否存在该推荐人以及该手机号是否匹配
            $refStaffExist = $dbStaff->where(array('staff_real' => $_POST['staffName'] ,'mobile' => $_POST['refPhoneNum']))->find();
            if($refStaffExist){
                $refData['referee'] = $refStaffExist['id'];
                $ref = $dbStaff->where('id='.$userid)->save($refData);
                if($ref){
                    $this->success('完善信息成功，正在跳转到个人页面', U('User/index'));exit();
                }

            }
           else{
                $this->error('推荐人和手机号不匹配！', U('User/compeleInfo'));
                //TODO 输出该手机号不存在或者推荐人不存在信息
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
        $this->display();
    }

	/* 我的页面 */
	public function my(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();

        $this->assign('resStaff',$resStaff);
	    $this->display();
	}

    /* 钱包 */
	public function walletDetails(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();

        $this->assign('resStaff',$resStaff);
	    $this->display();
    }

    /* 我的银行卡 */
    public function myCard(){
	    $this->display();
    }

    /* 我的任务 */
    public function task(){
        $this->display();
    }

    /* 分享二维码 */
    public function share(){
        $this->display();
    }

    /* 个人设置 */
    public function set(){
        $this->display();
    }

    /* 我的报表 */
    public function financialStatements(){
        $this->display();
    }

    /* 消息管理 */
    public function infoManagement(){
        $this->display();
    }

    /* 奖励中心 */
    public function encourage(){
        $dbReward  = M('reward');
        $userid    = $_SESSION['userid'];
        //游戏分红
        $bonusReward = $dbReward->where(array('uid' => $userid , 'type' => 1))->select();
        //任务奖励
        $taskReward = $dbReward->where(array('uid' => $userid , 'type' => 2))->select();
        //推荐奖励
        $map['create_time'] = array('EGT',date('Y-m-d 00:00:00'));
        $map['uid'] = array('EQ',$userid);
        $map['type'] = array('EQ',3);
        $spreadReward = $dbReward->where($map)->select();
        //输出模板
        $this->assign('bonusReward',$bonusReward);
        $this->assign('taskReward',$taskReward);
        $this->assign('spreadReward',$spreadReward);
        $this->display();
    }

    /* 账单管理 */
    public function rechargeWithdrawCash(){
        $this->display();
    }

    /* 推广管理 */
    public function spreadManage(){
        $this->display();
    }

    /* 客服中心 */
    public function customService(){
        $this->display();
    }

    /* 任务大厅 */
    public function taskOffice1(){
        $this->display();
    }

    /* 宣传中心 */
    public function propagate(){
        $this->display();
    }



}
