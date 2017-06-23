
<?php
require_once "../lib/WxPay.Api.php";
require_once "WxPay.MicroPay.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

if(count($_POST)!= 0){

    $auth_code = $_POST[0];   //授权码
    $money = round($_POST[1]);//换成整型
    //$money = $_POST[1]*100; //真实的金额
    $input = new WxPayMicroPay();
    $input->SetAuth_code($auth_code);
    $input->SetBody("刷卡测试样例-支付");//也可以设置为会员手机号
    $input->SetTotal_fee($money);     //支付金额
    $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));

    /***获得会员手机号码进行处理***/
    $vip_call_no = $_POST[2];
    //var_dump($input);die();
    /***获得会员手机号码进行处理***/

    $microPay = new MicroPay();
    printf_info($microPay->pay($input,$vip_call_no));//增加一个参数，用来传递会员手机号码

}

/**
 * 注意：
 * 1、提交被扫之后，返回系统繁忙、用户输入密码等错误信息时需要循环查单以确定是否支付成功
 * 2、多次（一半10次）确认都未明确成功时需要调用撤单接口撤单，防止用户重复支付
 */

?>
