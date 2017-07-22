<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/29
 * Time: 16:02
 */
namespace Home\Controller;
use User\Api\UserApi;

class WalletController extends HomeController{

    /** 钱包 */
    public function walletDetails(){
        header('Content-Type:text/html;charset=utf-8');
        $dbStaff  = D('staff');
        $user_id   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$user_id)->find();
        //提现日期
        $week = M('parameter')->where(array('name'=>'提现日期'))->getField('value');
        //获取现在日期
        $now_week = date('w',time());
        if($week == $now_week){
            $resStaff['week'] = 1;
        }else{
            $resStaff['week'] = 0;
        }
        $this->assign('staff',$resStaff);
        $this->display('Wallet/walletDetails');
    }

    /**游戏币充值**/
    public function recharge(){
        $staff = M('staff');
        $user_id   = $_SESSION['userid'];
        $consume_coin = $staff->field('game_id,consume_coin')->where(array('id'=>$user_id))->find();
        if(IS_POST){
            $game_id = I('game_id');
            $game_coin = I('coin');
            if($game_id == $consume_coin['game_id']){
                $type = 1;
            }else{
                $type = 2;
            }
            $order_id = make_orderId();//生成订单号
            //调用接口，判断返回值
            $result_api = $this->takeApi($game_id,$game_coin,$order_id);
            if($result_api['error'] == 0 && $result_api['data'] == "success"){
                //如果充值成功
                $charge = M('charge');
                $flow = M('flow');
                $notice = M('notice');
                $staff->startTrans();//启用事务
                $update_data = M('staff')->where(array('id'=>$user_id))->setDec('consume_coin',$game_coin);
                $flowData = array(
                    'uid'=>$user_id,
                    'type'=>4,
                    'money'=>$game_coin,
                    'order_id'=>$order_id,
                );
                $flow_add = $flow->add($flowData);
                $chargeData = array(
                    'pay_id'=>$user_id,
                    'game_id'=>$game_id,
                    'money'=>$game_coin,
                    'money'=>$game_coin,
                    'type'=>$type,
                    'order_id'=>$order_id,
                );
                $charge_add = $charge->add($chargeData);
                $dateNotice = array(
                    'uid'           => $user_id,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> '3',
                    'notice_title'  => '充值消息提醒',
                    'notice_content'=> "您于".date('Y-m-d H:i:s',time())."为账号ID:".$game_id."成功充值".$game_coin."元",
                );
                $notice_add = $notice -> add($dateNotice);
                if($update_data && $flow_add && $charge_add && $notice_add){
                    $data['code'] = 1;
                    $staff->commit();//成功则提交
                }else{
                    error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$user_id."已充值成功但是本地数据未更新\n", 3, "/playCharge_err.log");
                    $staff->rollback();//不成功，则回滚
                }
            }else{
                $data['code'] = 2;
                $data['info'] = urldecode($result_api['data']);
            }
            $this->ajaxReturn($data,"JSON");
        }
        $this->assign('consume_coin',$consume_coin['consume_coin']);
        $this->display('Wallet/recharge');
    }

    /**提现**/
    public function cash(){
        $staff = M('staff');
        $user_id   = $_SESSION['userid'];
        $money = $staff->where(array('id'=>$user_id))->getField('money');
        $bank = M('user_bank');
        $list = $bank->where(array('user_id'=>$user_id))->select();
        if(!empty($list)){
            foreach ($list as $key=>$value){
                $list[$key]['card'] = substr($value['bank_card'],-4);
            }
        }
        if(IS_POST){
            //首先判断今天是否已经提过现
            $start_time = date("Y-m-d 00:00:00",time());
            $end_time = date("Y-m-d 23:59:59",time());
            $map['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $map['uid'] = $user_id;
            $is_withdraw = M('withdraw')->where($map)->find();
            if(!empty($is_withdraw)){
                $data['code'] = 3;
            }else{
                $bank_card = I('card');
                $bank_message = M('user_bank')->where(array('user_id'=>$user_id,'bank_card'=>$bank_card))->find();
                $post_money = I('money');
                $fee = M('parameter')->where(array('name'=>'提现税率'))->getField('value');
                $order_id = make_orderId();//生成订单号
                $cashMsg = array('id'=>$user_id,'money'=>$post_money);
                $fee_money = $post_money * $fee/100;
                $fact_money = $post_money - $fee_money;
                $bank_name = '"'.$bank_message['bank_name'].'"';
                $subbranch = '"'.$bank_message['subbranch'].'"';
                $bank_holder = '"'.$bank_message['holder_name'].'"';
                $userMsg = array(
                    'uid'=>$user_id,
                    'money'=>$post_money,
                    'fact_money'=>$fact_money,
                    'bank_name'=>$bank_name,
                    'subbranch'=>$subbranch,
                    'card'=>$bank_card,
                    'bank_holder'=>$bank_holder,
                    'order_id'=>$order_id,
                );
                $flowMsg = array(
                    'uid'=>$user_id,
                    'type'=>5,
                    'money'=>$post_money,
                    'order'=>$order_id
                );
                $time = date('Y-m-d H:i:s');
                $content = "您于".$time."提现".$post_money.",手续费".$fee_money."请在财务管理中查看提现状态！";
                $noticeMsg = array(
                    'uid'           => $user_id,
                    'kind'          => '2',
                    'poster'        => 'system',
                    'notice_type_id'=> '3',
                    'notice_title'  => '提现消息',
                    'notice_content'=> $content,
                );
                $result_cash = getCash($cashMsg,$userMsg,$flowMsg,$noticeMsg);
                if($result_cash == "success"){
                    $data['code'] = 1;
                }else{
                    $data['code'] = 2;
                }
            }
            $this->ajaxReturn($data,"JSON");
        }
        $this->assign('list',$list);
        $this->assign('money',$money);
        $this->display('Wallet/cash');
    }

    /**调用充值接口**/
    public function takeApi($uid,$money,$orderNo){
        $api = A('index');
        $api -> getApi();
        /*取值*/
        $url = "http://119.23.60.80/admin/napp";
        $post_data = "api=pay&uid=".$uid."&money=".$money."&orderNo=".$orderNo."&payType=api";
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
        $api_recharge = std_class_object_to_array($api_data);
        return $api_recharge;
    }
}
