<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Think\Controller;

class CashController extends AdminController {

    /**提取起止日期 表中字段是create_time**/
    public function _queryCreateTime(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        if($start_time || $end_time){
            if($start_time >= $end_time){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['create_time'] = array(array('gt', $start_time), array('lt', $end_time));
            }
        }
        return $map;
    }

    /**提取起止日期 表中字段是post_time**/
    public function _queryPostTime(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        if($start_time || $end_time){
            if($start_time >= $end_time){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['post_time'] = array(array('gt', $start_time), array('lt', $end_time));
            }
        }
        return $map;
    }


    /**
     * 分红奖励记录
     * @param null $method
     */
    public function cashGiven($method = null){
        $reward = M('reward');
        $map = $this -> _queryCreateTime();
        if(is_numeric(I('mobile'))){
            $mobile = I('mobile');
            $uid = M('staff')->where(array('mobile'=>$mobile))->getField('id');
            if($uid){
                $map['uid'] = $uid;
            }
        }
        $map['type'] = 1;
        $results = $this->lists($reward,$map);

        if(!empty($results)){
            $result_list = D('reward')->getBonus($results);
        }else{
            $result_list = $results;
        }
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        //excel表的表头字段
        $xlsCell = array(
            array('number', '编号'),
            array('real_name', '用户姓名'),
            array('mobile', '手机号'),
            array('credit_value', '信用值分'),
            array('fix_bonus', '固定分红点'),
            array('extra_bonus', '额外分红点'),
            array('money', '奖励金额'),
            array('create_time', '奖励时间'),
            array('order_id', '奖励单号'),
            array('remarks', '备注')
        );

        //excel表标题
        if(empty($map)){
            $xlsName = "所有数据导出";
        }else{
            $xlsName = "按照搜索条件数据导出";
        }

        //按照输出方式进行展现
        switch (strtolower($method)){
            case 'list':
                $result_list;
                break;
            case 'out':
                $excel->exportExcel($xlsName,$xlsCell,$result_list);
                break;
        }
        int_to_string($result_list);
        $this->assign('list',$result_list);
        $this -> meta_title = '分红管理';
        $this -> display('Main/Cash/cashGiven');
    }


    /**
     * 出入账管理
     * @param null $method
     */
    public function cashIo($method = null){
        if(IS_POST){
            $id = $_POST['id'];
            if($_POST['submit'] == 'submit'){
                $handle_time = date("Y-m-d H:i:s",time());
                $result = D('withdraw')->updateData(array('id'=>$id),array('status'=>2,'handle_time'=>$handle_time,'admin'=>session('user_auth')['username'],'ip'=>$_SERVER['REMOTE_ADDR']));
                if($result){
                    $data['code'] = 1;
                }else{
                    $data['code'] = 2;
                }
                $this->ajaxReturn($data,'JSON');
            }
        }
        //设置默认状态
        if(!I('nav_model')){
            $nav = 1;
        }else{
            $nav = I('nav_model');
        }
        //按照时间查询
        $map = $this -> _queryCreateTime();
        //按照手机号查询
        if(is_numeric(I('mobile'))){
            $mobile = I('mobile');
            $uid = M('staff')->where(array('mobile'=>$mobile))->getField('id');
            if($uid){
                $map['uid'] = $uid;
            }
        }

        /***判断是提现管理还是充值管理页面展示***/
        if($nav == 1){//提现管理
            $withdraw = M('withdraw');//实例化对象
            //判断是否按照类型查询
            if(I('status')){
                $map['status'] = I('status');
                $status['value'] = I('status');
            }
            $results = $this->lists($withdraw,$map);
            if(!empty($results)){
                $result_list = D('withdraw')->getCash($results);
            }else{
                $result_list = $results;
            }
            //导出查询到的数据（不含分页）
            $excel = A('Excel');
            //excel表的表头字段
            $xlsCell = array(
                array('number', '编号'),
                array('real_name', '用户姓名'),
                array('mobile', '手机号'),
                array('bank_name', '银行类型'),
                array('subbranch', '开户支行'),
                array('card', '银行卡号'),
                array('bank_holder', '开户人'),
                array('money', '提现金额'),
                array('fee', '税率(%)'),
                array('fact_money', '到账金额'),
                array('create_time', '申请时间'),
                array('handle_time', '处理时间'),
                array('statue', '提现状态')
            );
            //excel表标题
            if(empty($map)){
                $xlsName = "所有数据导出";
            }else{
                $xlsName = "按照搜索条件数据导出";
            }

            //按照输出方式进行展现
            switch (strtolower($method)){
                case 'list':
                    $result_list;
                    break;
                case 'out':
                    $excel->exportExcel($xlsName,$xlsCell,$result_list);
                    break;
            }
            int_to_string($result_list);
            $this->assign('list',$result_list);
        }else{//充值管理
            $charge = M('charge');//实例化对象
            //判断是否按照类型查询
            if(I('type')){
                $map['type'] = I('type');
                $vo['type'] = I('type');
            }
            $results = $this->lists($charge,$map);
            if(!empty($results)){
                $result_lists = D('charge')->getCharge($results);
            }else{
                $result_lists = $results;
            }
            //导出查询到的数据（不含分页）
            $excel = A('Excel');
            //excel表的表头字段
            $xlsCell = array(
                array('number', '编号'),
                array('real_name', '支付人'),
                array('game_id', '充值游戏账号'),
                array('money', '充值金额'),
                array('money', '充值金额'),
                array('create_time', '充值时间')
            );
            //excel表标题
            if(empty($map)){
                $xlsName = "所有数据导出";
            }else{
                $xlsName = "按照搜索条件数据导出";
            }

            //按照输出方式进行展现
            switch (strtolower($method)){
                case 'list':
                    $result_lists;
                    break;
                case 'out':
                    $excel->exportExcel($xlsName,$xlsCell,$result_lists);
                    break;
            }
            int_to_string($result_lists);
            $this->assign('lists',$result_lists);
        }
        $this->assign('nav_model',$nav);
        $this->assign('status',$status);
        $this->assign('vo',$vo);
        $this -> meta_title = '出入帐理';
        $this -> display('Main/Cash/cashIo');
    }


    /**财务总表**/
    public function cashTotal(){
        //平台推广专员注册收入
        $re_income = M('re_charge')->sum('money');
        //游戏平台充值收入
        $recharge = M('user_charge')->sum('money');
        //平台收入:推广专员注册资金+游戏充值总额；
        $income = $re_income + $recharge;
        $this->assign('income',$income);
        /**平台支出**/
        //奖励支出
        $reward = D('reward');
        $reward_cash = $reward->getcash();
        $this->assign('reward',$reward_cash);
        //提现总额
        $withDraw = D('Withdraw');
        $withDraw_msg = $withDraw->getMoney('fact_money');
        $this->assign('withdraw',$withDraw_msg);
        //余额与游戏币
        $staff = D('staff');
        $staff_msg = $staff->getCash('money,consume_coin');
        $this->assign('overplus',$staff_msg);
        //系统支出 = 奖励支出
        $system_spend = array_sum($reward_cash);
        //实际支出 = 提现总额
        $fact_spend = $withDraw_msg;
        //实际拨比
        $percent['fact_percent'] = $fact_spend/$income*100;
        //系统拨比
        $percent['system_percent'] = $system_spend/$income*100;
        $this->assign('percent',$percent);
        $this->assign('system_spend',$system_spend);
        $this -> meta_title = '财务总表';
        $this -> display('Main/Cash/cashTotal');
    }


    /**
     * 奖励明细
     * @param null $method
     */
    public function cashDetail($method = null){
        $reward = M('reward');
        $map = $this -> _queryCreateTime();
        if(is_numeric(I('mobile'))){
            $mobile = I('mobile');
            $uid = M('staff')->where(array('mobile'=>$mobile))->getField('id');
            if($uid){
                $map['uid'] = $uid;
            }
        }
        if(I('get.type')){
            $map['type'] = I('get.type');
            $type['value'] = I('type');
        }else{
            $map['type'] = array('gt',1);    
        }

        $results = $this->lists($reward,$map);
        if(!empty($results)){
            $result_list = D('reward')->getAllBonus($results);
        }else{
            $result_list = $results;
        }
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        //excel表的表头字段
        $xlsCell = array(
            array('number', '编号'),
            array('real_name', '用户姓名'),
            array('mobile', '手机号'),
            array('type', '奖励类型'),
            array('money', '奖励金额'),
            array('create_time', '奖励时间'),
            array('order_id', '奖励单号'),
            array('remarks', '备注')
        );

        //excel表标题
        if(!I('start_time') || !I('end_time') || !I('mobile') || !I('type')){
            $xlsName = "所有数据导出";
        }else{
            $xlsName = "按照搜索条件数据导出";
        }

        //按照输出方式进行展现
        switch (strtolower($method)){
            case 'list':
                $result_list;
                break;
            case 'out':
                $excel->exportExcel($xlsName,$xlsCell,$result_list);
                break;
        }
        int_to_string($result_list);
        $this->assign('list',$result_list);
        $this->assign('type',$type);
        $this -> meta_title = '奖励明细';
        $this -> display('Main/Cash/cashDetail');
    }


























}
