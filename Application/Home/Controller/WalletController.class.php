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

    /* 钱包 */
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
        $this->display('walletDetails');
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
            //调用接口，判断返回值
            //如果充值成功
            $order_id = make_orderId();//生成订单号
            $re = M()->execute("call pro_recharge($user_id,$game_id,$game_coin,$order_id,$type,4)");
            if($re == 1){
                $data['code'] = 1;
            }
            $this->ajaxReturn($data,"JSON");
        }
        $this->assign('consume_coin',$consume_coin['consume_coin']);
        $this->display('recharge');
    }

    /**提现**/
    public function cash(){
        $staff = M('staff');
        $user_id   = $_SESSION['userid'];
        $money = $staff->where(array('id'=>$user_id))->getField('money');
        $bank = M('user_bank');
        $user_id   = $_SESSION['userid'];
        $list = $bank->where(array('user_id'=>$user_id))->select();
        if(!empty($list)){
            foreach ($list as $key=>$value){
                $list[$key]['card'] = substr($value['bank_card'],-4);
            }
        }
        if(IS_POST){
            //首先判断今天是否已经提过现
            $times = date('Y-m-d',time());
            $is_withdraw = M('withdraw')->where(array('uid'=>$user_id,'create_time'=>array('like', '%' . (string)$times . '%')))->find();
            if(!empty($is_withdraw)){
                $data['code'] = 3;
            }else{
                $bank_card = I('card');
                $bank_message = M('user_bank')->where(array('user_id'=>$user_id,'bank_card'=>$bank_card))->find();
                $fee = M('parameter')->where(array('name'=>'提现税率'))->getField('value');
                $order_id = make_orderId();//生成订单号
                $money = I('money');
                $fact_money = $money * (100-$fee)/100;
                $bank_name = '"'.$bank_message['bank_name'].'"';
                $subbranch = '"'.$bank_message['subbranch'].'"';
                $bank_holder = '"'.$bank_message['holder_name'].'"';
                $re = M()->execute("call pro_withdraw($user_id,$money,$fee,$fact_money,$bank_name,$subbranch,$bank_card,$bank_holder,$order_id,5)");
                if($re == 1){
                    $data['code'] = 1;
                }else{
                    $data['code'] = 2;
                }
            }
            $this->ajaxReturn($data,"JSON");
    }
        $this->assign('list',$list);
        $this->assign('money',$money);
        $this->display('cash');
    }
}
