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

	/* 用户中心首页 */
	public function index(){

	}

	/* 登录页面 */
	public function login(){
		if(IS_POST){ //登录验证
            $dbStaff  = M('staff');
            $username = $_POST['username'];
            $password = $_POST['password'];
            $regStaff = $dbStaff ->where(array('mobile' => $username))->find();
                if($regStaff){ //验证手机号
                    if( $regStaff['staff_pwd']== md5($password)){ //验证密码
                        if($regStaff['status']==1){//验证该用户状态
                            $_SESSION['userid'] = $regStaff['id'];    //用户id
                            //TODO 判断该用户是否完善信息
                              $dbUser = $dbStaff->where('id='.$_SESSION['userid'])->find();
                                if(($dbUser['card_id']==null)||($dbUser['referee']==null) || ($dbUser['game_id']==null)){
                                    $this->success( '登陆成功，正在跳转到完善信息页面!',U('User/compeleInfo'));
                            }else{
                                $this->success( '登陆成功，正在跳转到主页面!',U('User/index'));
                            }
                        }
                        if($regStaff['status']==2){//该用户处于禁用状态
                            $this->error( '登录失败，该账号被禁用！',U('Login/login') );
                        }
                        if($regStaff['status']==3){//该用户处于未缴费状态
                            $this->error( '登录失败，该账号未缴费！',U('Login/login') );
                        }
                        if($regStaff['status']==-1){//该用户处于删除状态
                            $this->error( '登录失败，该账号被删除！',U('Login/login') );
                        }

                    }else{ //手机号或密码错误
                        $this->error( '密码错误,请重新输入!',U('Login/login') );
                    }
                }else{
                    $this->error( '该手机号不存在!',U('Login/login') );
                }
		} else { //显示登录表单
			$this->display();
		}
	}

	/* 退出登录 */
	public function logout(){
	    //销毁session
        session('[destroy]');
	}

    /* 注册页面 */
    public function register(){
        //判断提交方式
        if( IS_POST ) {
            //解决中文乱码
            header('Content-Type:text/html;charset=utf-8');
            //如果过期验证码失效
            if ($_SESSION['verifyNum']['time'] < time()) {
                unset($_SESSION['verifyNum']['content']);
            }
            //判断手机验证码
            if ($_SESSION['verifyNum']['content'] == $_POST['verifyNum']) { //$_SESSION['verifyNum']['content'] == $_POST['verifyNum']
                //实例化staff对象
                $dbStaff = D('staff');
                //判断手机号重复
                if ($dbStaff->where('mobile=' . $_POST['phoneNum'])->select()) {
                    $this->error('该手机号已注册！', U('Login/register'));
                } else {//获取提交信息

                    $refStaff = array(
                        'mobile' => $_POST['phoneNum'],                 //注册手机号码
                        'staff_pwd' => md5($_POST['password1']),        //密码md5
                        'staff_real' => $_POST['staffName'],             //个人姓名
                        'create_time' => date('y-m-d h:i:s', time()),   //创建时间
                        'status' => 3,                                  //注册未缴费状态
                    );
                        //插入一条新用户记录
                        $ref = $dbStaff->add($refStaff);
                        if ($ref) {//清除session指定字段的值
                        unset($_SESSION['verifyNum']['content']);
                        //跳转微信支付
                        $customerid = 102090;                               //商户在网关系统上的商户号 TODO 获得商户号
                        $sdcustomno = $customerid . time() . rand(1000000, 9999999);//订单在商户系统中的流水号 商户信息+日期+随机数
                        $orderAmount = 1;                                   //订单支付金额；单位:分(人民币)
                        $cardno = 51;                                       //微信wap  (固定值 51)
                        $key = 'b0308d76c651420ce1e4662f36dc11ee';          //       TODO 获得key
                        $noticeurl = 'http://' . $_SERVER['HTTP_HOST'] . '/home/login/wxcallback';    //在网关返回信息时通知商户的地址,该地址不能带任何参数，否则异步通知会不成功
                        $backurl = 'http://' . $_SERVER['HTTP_HOST'] . '/home/login/registerSucc';    //在网关返回信息时回调商户的地址,跳转到完善信息页面
                        //sign进行加密
                        $Md5str = 'customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . $key;
                        $sign = strtoupper(md5($Md5str));//发送给网关的签名字符串,为以上参数加商户在网关系秘钥（key）一起按照顺序MD5加密并转为大写的字符串
                        $mark = $refStaff['mobile'];     //商户自定义信息，不能包含中文字符，因为可能编码不一致导致MD5加密结果不一致,返回改手机号码 然后查询该纪录
                        //拼接url
                        $url = 'http://www.51card.cn/gateway/weixinpay/wap-weixinpay.asp?customerid=' . $customerid . '&sdcustomno=' . $sdcustomno . '&orderAmount=' . $orderAmount . '&cardno=' . $cardno . '&noticeurl=' . $noticeurl . '&backurl=' . $backurl . '&sign=' . $sign . '&mark=' . $mark;
                        //跳转url
                        Header("HTTP/1.1 303 See Other");
                        Header("Location: $url");
                    } else {//插入新用户记录失败
                        $this->error('注册失败，请联系管理员！', U('Login/register'));
                    }
                }

            } else {
                $this->error('验证码错误！', U('Login/register'));
            }


        }

        $this->display();
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
            $mark       =   $_REQUEST['mark'];           //未启用暂时返回空值//返回用户手机号码
            $sign       =   $_REQUEST['sign'];           //发送给商户的签名字符串
            $resign     =   $_REQUEST['resign'];         //发送给商户的二次签名字符串
            $des        =   $_REQUEST['des'];            //描述订单支付成功或失败的系统备注
            $key        =   'b0308d76c651420ce1e4662f36dc11ee'; //TODO 获取key
            //sign第一次加密结果
            $signRef    =   md5('customerid='.$customerid.'&sd51no='.$sd51no.'&sdcustomno='.$sdcustomno.'&mark='.$mark.'&key='.$key);
            //验证sign
            $yzsign     =   strtoupper($signRef);
            //验证resign
            $yzresign   =   strtoupper(md5('sign='.$signRef.'&customerid='.$customerid.'&ordermoney='.$ordermoney.'&sd51no='.$sd51no.'&state='.$state.'&key='.$key));
            //验证sign resign
            if(($yzsign == $sign)&&($yzresign == $resign)){
                //验证充值状态以及缴费金额
                if($state==1 && $ordermoney>=1000){ //$state==1 && $ordermoney>=1000
                    //回调参数获得该用户手机号码
                    $mobile = $mark;
                    //实例化staff
                    $dbStaff = D('staff');
                    //字段参数设置为1,代表可用状态
                    $refStaff['status'] = 1;
                    //根据条件更新记录
                    $dbStaff->where('mobile='.$mobile)->save($refStaff);
                    //保存session方便跳转后获得该用户手机号进行完善信息
                    $_SESSION['mobile'] = $mobile;
                    //返回1给网关
                    echo '<result>1</result>';
                    //网关返回信息回调本商户地址
                }
                else{//充值失败或充值金额不足

                }

            }else{//验证失败

            }
        }
    }

    /* 注册成功 */
    public function registerSucc()
    {
        $this->display();
    }

    /* 密码找回 */
    public function getNewPsd()
    {
        if(IS_POST){
            $phoneNum  = $_POST['phoneNum'];
            $verifyNum = $_POST['verifyNum'];
            $password1 = $_POST['password1'];
            $password2 = $_POST['password2'];

            if( $_SESSION['verifyNum']['content'] == $verifyNum ){     //判断验证码
                $dbStaff = M('staff');                                 //实例化表
                if( $dbStaff->where('mobile='.$phoneNum)->find() ){    //验证该用户是否存在
                    $RegStaff['staff_pwd'] = md5($password1);          //存储密码
                    if($dbStaff->where('mobile='.$phoneNum)->save($RegStaff)){
                        //清空session指定字段值
                        unset($_SESSION['verifyNum']);
                        $this->success('修改成功',U('Login/login'));
                        exit();
                    }

                }else{
                    $this -> error('该用户不存在!',U('Login/getNewPsd'));
                }
            }else{
                $this -> error('验证码错误！',U('Login/getNewPsd'));
            }

        }
        $this->display();
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

}
