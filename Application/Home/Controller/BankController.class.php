<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/7/4
 * Time: 17:03
 */
namespace Home\Controller;
class BankController extends HomeController{

    /**我的银行卡**/
    public function myCard(){
        $bank = M('user_bank');
        $user_id   = $_SESSION['userid'];
        $list = $bank->where(array('user_id'=>$user_id))->select();
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $list[$key]['bank_card'] = substr_replace($val['bank_card'], '**** ****', 4, 8);
            }
        }
        $this->assign('list',$list);
        $this->display('bank/myCard');
    }

    /**绑定银行卡**/
    public function bindCard(){
        $bank = M('user_bank');
        $user_id   = $_SESSION['userid'];
        if(IS_POST){
            //判断银行是否已被绑定
            $card = I('card');
            $bank_card = $bank->where(array('bank_card'=>$card))->find();
            if(!empty($bank_card)){
                $data['code'] = 3;
            }else{
                $bank_name = I('bank');
                switch ($bank_name){
                    case 2:$name = "农业银行";break;
                    case 3:$name = "工商银行";break;
                    case 4:$name = "建设银行";break;
                    default:$name = "中国银行";break;
                }
                $insert_data['user_id'] = $user_id;
                $insert_data['bank_card'] = I('card');
                $insert_data['bank_name'] = $name;
                $insert_data['subbranch'] = I('subbranch');
                $insert_data['holder_name'] = I('holder_name');
                $res = $bank->add($insert_data);
                if($res){
                    $data['code'] = 1;
                }else{
                    $data['code'] = 2;
                }
            }
            $this->ajaxReturn($data,"JSON");
        }
        $this->display('bank/bindCard');
    }
}
