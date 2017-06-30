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
class FinancialController extends HomeController {

    /* 报表 */
    public function financialStatements(){

        $this->display();
    }

    /* 报表信息返回 */
    public function financialReturn(){

        $data = array(
            array(
                '0' => 1, //第一下标必须是0
                '1' => 2,
                '2' => 3,
                '3' => 4,
            ),//日期
            array(
                '0' => 3, //第一下标必须是0
                '1' => 4,
                '2' => 5,
            ),//充值
            array(
                '0' => 6, //第一下标必须是0
                '1' => 7,
                '2' => 8,
            ),//提现
        );
        echo json_encode($data);
    }


}
