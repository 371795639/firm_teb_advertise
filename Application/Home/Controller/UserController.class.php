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
        if(IS_POST) {
            //解决中文乱码
            header('Content-Type:text/html;charset=utf-8');
            $dbStaff = D('staff');
            $userid = $_SESSION['userid'];
            if ($_GET['type'] == 'league') {
                $game_id = $_POST['game_id'];
		        //首先调用接口确认该游戏账号是否存在
                $is_game = $this->idExist($game_id);
                if($is_game['error'] == 0 && $is_game['data'] == "success"){
                    //存入表中
                     $res = $dbStaff->where(array('id'=>$userid))->save(array('game_id'=>$game_id,'status'=>1));
                     if($res){
                            $this->redirect('User/index');
                         }else{
                            echo "<script>alert('绑定失败，请重新绑定!');history.back(-1);</script>";
                         }
                }else{
                     echo "<script>alert('该游戏ID不存在，请重新填写正确账户ID!');history.back(-1);</script>";
                }
                
            } else {
                $refData = array(
                    'game_id' => $_POST['gameId'],
                    'card_id' => $_POST['cardNum'],
                    'address' => $_POST['address'],
                    'status' => 1,  //1：正常；2：禁用；3：未完善信息
                );
                if (empty($_POST['refPhoneNum'])) {
                    //用户上次注册时未完善信息，再次登陆的时候，将跳转到完善信息页面
                } else {
                    $refStaffExist = $dbStaff->where(array('staff_real' => $_POST['staffName'], 'mobile' => $_POST['refPhoneNum']))->find();
                    if ($refStaffExist) {
                        $cardId = $dbStaff->where(array('card_id' => $refData['card_id']))->select();
                        if ($cardId) {
                            echo "<script>alert('此身份证号已注册过，不能再注册哦!');</script>";
                        } else {
                            if ($refStaffExist['id'] == $userid) {
                                echo "<script>alert('推荐人不能是自己!');</script>";
                            } else {
                                //首先调用接口确认该游戏账号是否存在
                                $is_game = $this->idExist($refData['game_id']);
                                if($is_game['error'] == 0 && $is_game['data'] == "success"){
                                	$gameId = $dbStaff->where(array('game_id' => $refData['game_id']))->select();
                                	if ($gameId) {
                                    	echo "<script>alert('此游戏ID已被占用，请检查输入');history.back(-1);</script>";
                                	}else{
                                    	//更新用户信息
                                   	 $refData['referee'] = $refStaffExist['id'];
                                   	 $refData['service_number'] = $refStaffExist['service_number'];
                                   	 $ref = $dbStaff->where('id=' . $userid)->save($refData);
                                    	//给推荐人recommend_num+1
                                    	$refDateRecommend['recommend_num'] = $refStaffExist['recommend_num'] + 1;
                                   	 $refAdd = $dbStaff ->  save_staff_by_id($refStaffExist['id'],$refDateRecommend);
                                   	 if($ref && $refAdd){
//                                        	recommend($refStaffExist['id']);
                                        	$this->redirect('User/index');
                                    	}
                                   }
				}else{
					echo "<script>alert('此游戏ID不存在，请重新正确填写！');history.back(-1);</script>";
				}
                            }
                        }
                    }else{
                        echo "<script>alert('推荐人和手机号不匹配!');window.history.back(-1);</script>";
                    }
                }
            }
        }
        $this->display('User/compeleInfo');
    }

    /* 主页面 */
    public function index(){
        $dbStaff  = D('staff');
        $userid   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$userid)->find();
        $this->assign('resStaff',$resStaff);
        /*显示剩余任务个数*/
        $dbTaskDone     = D('TaskDone');
        $resDoneCount   = $dbTaskDone -> get_all_task($_SESSION['userid'],'','1');
        $doingNo        = $dbTaskDone -> get_count($resDoneCount,'status',1);
        $this->assign('doingNo',$doingNo);
        /*根据当前时间更换问候图片*/
        $time = date('H:i:s');
        if($time <= "12:00:00"){
            $pic = 1;
        }else{
            $pic = 2;
        }
        /**信用值**/
        $credit = D('StaffInfo') -> get_staff_by_uid($userid);
        $creditValue = $credit['credit_value'];
        //判断是否是兑换中心
        if($resStaff['is_service'] == 1) {
            $total['type'] = 1;
            /*推广业绩总额*/
            $res_count = M('staff')->where(array('is_league'=>0,'service_number'=>$resStaff['service_number']))->count();
            $total['recommend'] = $res_count * 1000;
            /*充值业绩总额*/
            $total['recharge'] = M('user_charge')->where(array('service_num'=>$resStaff['service_number'],'type'=>1))->sum('money');
        }
        $this->assign('creditValue',$creditValue);
        $this->assign('total',$total);
        $this->assign('pic',$pic);
        $this->display('User/index');
    }

	/* 我的页面 */
	public function my(){
        $dbStaff        = D('staff');
        $dbStaffInfo    = D('StaffInfo');
        $resStaff       = $dbStaff      -> get_staff_by_id($_SESSION['userid']);
        $resStaffInfo   = $dbStaffInfo  -> get_staff_by_uid($_SESSION['userid']);
        $class          = $resStaffInfo['class'];
        if($resStaff['is_league'] == 0){
            $re = '推广专员';
        }else{
            $res = '级加盟商';
            switch($class){
                case 1:$re = '一'.$res;break;
                case 2:$re = '二'.$res;break;
                case 3:$re = '三'.$res;break;
            }
        }
        $this->assign('re',$re);
        $this->assign('resStaff',$resStaff);
	    $this->display('User/my');
	}

    /* 奖励中心 */
    public function encourage(){
        $dbReward  = M('reward');
        $userid    = $_SESSION['userid'];
        //类型 1：日常任务奖励(包含分红)、2：额外任务奖励、3：推荐奖励、4：充值提成；5中心推荐奖励；6中心业绩奖励;7:分销奖励；
        $date       = date('Y-m-d H:i:s');
        $dbDone     = D('TaskDone');
        $monday     = $dbDone -> get_start_time($date);
        $sunday     = $dbDone -> get_end_time($date);
        $map = array(
            'uid'           => $userid,
            'create_time'   => array(array('gt',$monday),array('lt',$sunday)),
            'type'          => array('in','1,2,3,4,5,6,7'),
        );
        $weekReward= $dbReward->where($map)->sum('money + game_coin');
        //任务奖励
        $taskReward = $dbReward->where(array('uid' => $userid , 'type' => array('in','1,2')))->order('create_time desc')->select();
        foreach ($taskReward as $key => $value) {
            $taskReward[$key]['money'] = $value['money'];
        }
        //推荐奖励
        $map =array(
            'uid'   => $userid,
            'type'  => array('in','3,4,5,6,7'),
        );
        $spreadReward = $dbReward->where($map)->order('create_time desc')->select();
        foreach ($spreadReward as $key => $value) {
            $spreadReward[$key]['money'] = $value['money'];
        }
        //输出模板
        $this->assign('weekReward',$weekReward);
        $this->assign('taskReward',$taskReward);
        $this->assign('spreadReward',$spreadReward);
        $this->display('User/encourage');
    }

    /* 财务管理 */
    public function rechargeWithdrawCash(){
        $dbwithdraw = M('withdraw');
        $dbcharge   = M('charge');
        $uid = $_SESSION['userid'];
        $currMinTime = date('Y-m-01 00:00:00',time());          //获取当前月份最小时间
        //本月提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且大于等于当前月份最小时间
//        $condition['status'] = array('eq',2 );                  //已提现
        $condition['create_time'] = array('EGT',$currMinTime);
        $preMonthWithdraw = $dbwithdraw->where($condition)->order('create_time desc')->select();
        //历史提现
        $condition['uid'] = array('eq',$uid );                  //等于uid且小于当前月份最小时间
//        $condition['status'] = array('eq',2 );                  //已提现
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
        $this->display('User/rechargeWithdrawCash');
    }

    /* 推广管理 */
    public function spreadManage(){
        header('Content-Type:text/html;charset=utf-8');
        $uid   = $_SESSION['userid'];
        $list = M('staff')->where(array('referee'=>$uid))->order('create_time desc')->select();
	$list_count = count($list);
        $game_id = M('staff')->where(array('id'=>$uid))->getField('game_id');
        $lists = M('user_ship')->where(array('recommend'=>$game_id,'superior'=>$uid))->order('reg_time desc')->select();
        $lists_count = count($lists);
        $this->assign('list',$list);
	$this->assign('list_count',$list_count);
        $this->assign('lists',$lists);
	$this->assign('lists_count',$lists_count);
        $this->display('User/spreadManage');
    }


    /**判断该游戏ID是否存在**/
    public function idExist($game_id){
        $api = A('index');
        $api -> getApi();
        /*取值*/
        $url = "http://119.23.60.80/admin/napp";
        $post_data = "api=userExist&uid=".$game_id;
        $cookie_file = '/data/tuiguang/cookie/cookie.txt';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($ch);
        curl_close($ch);
        $api_data = json_decode($response);
        $api_status = std_class_object_to_array($api_data);
        return $api_status;
    }

}
