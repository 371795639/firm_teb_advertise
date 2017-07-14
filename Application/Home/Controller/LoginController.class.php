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
class LoginController extends \Think\Controller {

    /* 登录页面 */
    public function login(){
        if(IS_POST){ //登录验证
            $dbStaff  = M('staff');
            $username = $_POST['username'];
            $password = $_POST['password'];
            $regStaff = $dbStaff ->where(array('mobile' => $username))->find();
            $_SESSION['userid'] = $regStaff['id'];    //用户id
            if($regStaff){ //验证手机号
                if($regStaff['staff_pwd']== md5($password)){ //验证密码
                    if($regStaff['pay_status'] == 3){//该用户处于缴费成功状态
                        if ($regStaff['status'] == 1) {//验证该用户处于可用状态
                            $this -> redirect('User/index');
                        }elseif($regStaff['status'] == 3){
                            if($regStaff['is_league'] == 1){//是加盟商
                                $this -> redirect('User/compeleInfo',array('type'=>'league'));
                            }else{
                                $this -> redirect('User/compeleInfo');
                            }
                        }else{
                            echo "<script>alert('用户被禁用，请联系管理员!');</script>";
                        }
                    }else{
                        echo "<script>alert('该账号支付失败，请到注册页重新填写注册信息并完成支付！');window.location.href='".U('Home/Login/register')."';</script>";
                    }
                }else{
                    echo "<script>alert('密码错误!');</script>";
                }
            }else{
                echo "<script>alert('该手机号不存在!');</script>";
            }
        }
        $this->display('Login/login');
    }


    /* 退出登录 */
    public function logout(){
        //销毁session
        session('[destroy]');
    }


    public function register(){
        $dbStaff = M('staff');
        //判断提交方式
        if( IS_POST ) {
            //解决中文乱码
            header('Content-Type:text/html;charset=utf-8');
            //如果过期验证码失效
            if ($_SESSION['verifyNum']['time'] < time()) {
                unset($_SESSION['verifyNum']['content']);
            }
            //判断手机验证码
//            if ($_SESSION['verifyNum']['content'] == $_POST['verifyNum']) {
            if ($_POST['verifyNum'] == 1) {
                if($_POST['password1'] !== $_POST['password2']){
                    echo "<script>alert('输入的两次密码不一致，再检查下!');</script>";
                }
                //获取记录
                $refStaff = array(
                    'mobile'     => $_POST['phoneNum'],              //注册手机号码
                    'staff_pwd'  => md5($_POST['password1']),        //密码md5
                    'staff_real' => $_POST['staffName'],             //个人姓名
                    'create_time'=> date('Y-m-d H:i:s'),             //创建时间
                    'status'     => 3,                               //未完善信息
                    'pay_status' => 1 ,                              //默认等待付款状态
                );
                //判断手机号重复
                $phoneRepeat =  $dbStaff->where('mobile=' . $_POST['phoneNum'])->find();
                if(empty($phoneRepeat)){ //表中未检测到用户的手机号，说明是新用户，新增记录
                    if($dbStaff->add($refStaff)){
                        unset($_SESSION['verifyNum']['content']);
                    }else{
                        echo "<script>alert('新增用户记录失败，请重新注册!');window.location.href='".U('Home/Login/register')."';</script>";
                    }
                }else{
                    if($phoneRepeat['pay_status'] == 3 && $phoneRepeat['status'] == 1){
                        echo "<script>alert('该手机号已注册，别再瞎注册了!');window.location.href='".U('Home/Login/login')."';</script>";
                        unset($_SESSION['verifyNum']['content']);
                        die;
                    }
                    if($phoneRepeat['pay_status'] !== 3){
                        $this->wxcallback();
//                        die;
                    }
                }
                //查表获得该用户id
                $ref =  $dbStaff->where('mobile=' . $refStaff['mobile'])->find();
                //添加session用户id信息
                $_SESSION['user_id'] = $ref['id'] ;
                //跳转微信支付
                $customerid = 102090;                               //商户在网关系统上的商户号 - 获得商户号
                $sdcustomno = $customerid . time() . rand(1000000, 9999999);//订单在商户系统中的流水号 商户信息+日期+随机数
                $orderAmount = 1;                                   //订单支付金额；单位:分(人民币)
                $cardno = 51;                                       //微信wap  (固定值 51)
                $key = 'b0308d76c651420ce1e4662f36dc11ee';          //获得key
                $noticeurl = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/Home/Login/wxcallback';    //在网关返回信息时通知商户的地址,该地址不能带任何参数，否则异步通知会不成功
                $backurl   = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/Home/Login/registerSucc';  //在网关返回信息时回调商户的地址,跳转到完善信息页面
                //sign进行加密
                $Md5str = 'customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . $key;
                $sign = strtoupper(md5($Md5str));//发送给网关的签名字符串,为以上参数加商户在网关系秘钥（key）一起按照顺序MD5加密并转为大写的字符串
                $mark = $ref['id'];     //商户自定义信息，不能包含中文字符，因为可能编码不一致导致MD5加密结果不一致,返回用户uid 然后查询该纪录
                $_SESSION['user_id'] = $ref['id'];
                //拼接url
                $url = 'http://www.51card.cn/gateway/weixinpay/wap-weixinpay.asp?customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . '&sign=' . $sign . '&mark=' . $mark;
                error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$url."\n", 3, "/tmp/tuiguang.log");
                //跳转url
                Header("HTTP/1.1 303 See Other");
                Header("Location: $url");
            }else{
                echo "<script>alert('验证码错误!');</script>";
            }
        }
        $this->display('Login/register');
    }


    /* 微信支付通知 */
    public function wxcallback(){
        //判断请求
        if($_REQUEST){
            $state      =   $_REQUEST['state'];          //1.充值成功 2.充值失败
            $customerid =   $_REQUEST['customerid'];     //商户注册的时候，网关自动分配的商户ID
            $sd51no     =   $_REQUEST['sd51no'];         //该订单在网关系统的订单号
            $sdcustomno =   $_REQUEST['sdcustomno'];     //该订单在商户系统的流水号
            $ordermoney =   $_REQUEST['ordermoney'];     //商户订单实际金额 单位：（元）
            $cardno     =   $_REQUEST['cardno'];         //支付类型，为固定值 51
            $mark       =   $_REQUEST['mark'];           //未启用暂时返回空值//返回用户id
            $sign       =   $_REQUEST['sign'];           //发送给商户的签名字符串
            $resign     =   $_REQUEST['resign'];         //发送给商户的二次签名字符串
            $des        =   $_REQUEST['des'];            //描述订单支付成功或失败的系统备注
            $key        =   'b0308d76c651420ce1e4662f36dc11ee'; //获取key
            //sign第一次加密结果
            $signRef    =   md5('customerid='.$customerid.'&sd51no='.$sd51no.'&sdcustomno='.$sdcustomno.'&mark='.$mark.'&key='.$key);
            //验证sign
            $yzsign     =   strtoupper($signRef);
            //验证resign
            $yzresign   =   strtoupper(md5('sign='.$sign.'&customerid='.$customerid.'&ordermoney='.$ordermoney.'&sd51no='.$sd51no.'&state='.$state.'&key='.$key));
            error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$_REQUEST."\n", 3, "/tmp/tuiguang.log");
            //实例化flow流水表 staff表 reg_charge表
            $dbFlow  = M('flow');
            $dbStaff = M('staff');
            $dbregCharge = M('reg_charge');
            //验证sign resign
            if(($yzsign == $sign)&&($yzresign == $resign)){
                //回调参数获得该用户id
                 $uid = $mark;
//                $uid = $_SESSION['user_id'];
                if($state == 1){//充值成功
                     if($ordermoney>=0.01){
                        //金额支付大于等于1000
                        $refStaff = array(
                            'pay_status' => 3,
                        );
                        //更新staff表支付状态为付款成功
                        $refStaff = $dbStaff->where(array('id'=>$uid))->save($refStaff);
                     }else{//支付金额不足1000
                         $refStaff = array(
                             'pay_status' => 2,
                         );
                         //更新staff表支付状态为付款失败
                         $refStaff = $dbStaff->where('id='.$uid)->save($refStaff);
                     }
                    //流水表记录
                    $refFlow = array(
                        'uid'   => $uid,
                        'type'  => 7,
                        'money' => 15,
                        'order_id' => $sdcustomno,
                    );
                    //注册资金记录
                    $refregCharge = array(
                        'pay_id'   => $uid,
                        'money'    => 15,
                        'order_id' => $sdcustomno,
                    );
                    //写入流水表,注册资金记录
                    $refFlow      =   $dbFlow->add($refFlow);
                    $refregCharge =   $dbregCharge->add($refregCharge);

                }else{//充值失败
                    $refStaff=array(
                        'pay_status' => 2,
                    );
                    //更新staff表支付状态
                    $dbStaff->where('id='.$uid)->save($refStaff);
                }
                //保存session方便跳转后获得该用户id号进行完善信息
                //$_SESSION['userid'] = $uid;
                if($refFlow && $refStaff && $refregCharge){
                    //返回1给网关
                    echo '<result>1</result>';
                }
            }else{//验证失败

            }
        }
    }


    /* 注册成功 */
    public function registerSucc()
    {
        //查Staff表
        //判断请求
        $dbStaff = M('staff');
        $refStaff = $dbStaff->where('id='.$_SESSION['userid'])->find();
        switch ($refStaff['pay_status']) //支付状态
        {
            //等待付款状态
            case 1:
                echo "<script>alert('等待付款状态,3秒后重新跳转到本页面！');window.location.href='".U('Login/registerSucc')."';</script>";
                break;
            //付款失败状态
            case 2:
                echo "<script>alert('付款失败状态，请重新注册！');window.location.href='".U('Login/register')."';</script>";
                break;
            //付款成功状态
            case 3:
                $refStaff = array(
                    'status' => 3,
                );
                //更新staff表用户的status状态为1
                $dbStaff->where('id='.$_SESSION['userid'])->save($refStaff);
                break;
        }
        $this->display('Login/registerSucc');
    }


    /* 密码找回 */
    public function getNewPsd()
    {
        if(IS_POST){
            $phoneNum  = $_POST['phoneNum'];
            $verifyNum = $_POST['verifyNum'];
            $password1 = $_POST['password1'];
            $password2 = $_POST['password2'];
            $dbStaff = M('staff');
            if( $_SESSION['verifyNum']['content'] == $verifyNum ){     //判断验证码
                if($password1 !== $password2){
                    echo "<script>alert('输入的两次密码不一致!');</script>";
                }else{
                    if( $dbStaff->where('mobile='.$phoneNum)->find() ){    //验证该用户是否存在
                        $RegStaff['staff_pwd'] = md5($password1);          //存储密码
                        if($dbStaff->where('mobile='.$phoneNum)->save($RegStaff)){
                            //清空session指定字段值
                            unset($_SESSION['verifyNum']);
                            $this -> redirect('Login/login');
                        }
                    }else{
                        echo "<script>alert('该用户不存在!');</script>";
                    }
                }
            }else{
                echo "<script>alert('验证码错误!');</script>";
            }
        }
        $this->display('Login/getNewPsd');
    }

    /* 短信宝验证 */
    public function msgVerify(){

        $statusStr =    array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $phoneNum  =    $_POST['phoneNum'];
        $smsapi    =    "http://api.smsbao.com/";
        $user      =    "turboet"; //短信平台帐号 turboet
        $pass      =    'ecb741d44c0751b001bb62edc47529e7'; //短信平台密码 ecb741d44c0751b001bb62edc47529e7
        $verifyNum =    rand(1000,9999);
        //session设置验证内容
        $_SESSION['verifyNum']['content'] = $verifyNum;
        //session设置过期时间10分钟
        $_SESSION['verifyNum']['time'] = time()+600;
        $content   =    "【兔儿宝】您的验证码为".$verifyNum."，在10分钟内有效。";//要发送的短信内容
        $phone     =    "$phoneNum";//要发送短信的手机号码
        $sendurl   =    $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result    =    file_get_contents($sendurl) ;
        echo $statusStr[$result];
    }


    /**协议**/
    public function decla(){
        $this -> display('Login/decla');
    }

}
