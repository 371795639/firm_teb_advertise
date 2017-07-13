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
                $update_data = M('staff')->where(array('id'=>$user_id))->setDec('consume_coin',$game_coin);
                if($update_data){
                    $re = M()->execute("call pro_recharge($user_id,$game_id,$game_coin,$order_id,$type,4)");
                    if($re == 1){
                        $data['code'] = 1;
                    }
                }else{
                    error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$user_id."充值成功但账户未扣款，流水为写入\n", 3, "/tmp/php_sql_err.log");
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
        $this->display('Wallet/cash');
    }

    /**调用充值接口**/
    public function takeApi($uid,$money,$orderNo){
        $api = A('index');
        $api -> getApi();
        /*取值*/
        $url = "http://119.23.60.80/admin/napp";
        $post_data = "api=pay&uid=".$uid."&money=".$money."&orderNo=".$orderNo."&payType=api";
        $cookie_file = dirname(__FILE__).'/cookie.txt';
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
