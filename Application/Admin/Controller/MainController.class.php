<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Think\Controller;

class MainController extends AdminController {

    private function _queryTime(){
        $start_time= strtotime(I('start_time'));
        $end_time= strtotime(I('end_time'));
        if($start_time || $end_time){
            if($start_time >= $end_time){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['create_time'] = array(array('gt', $start_time), array('lt', $end_time));
            }
        }
        $map['status']  =   array('egt',0);
        return $map;
    }


    /**加载项目首页**/
    public function index(){
        $this -> meta_title = '项目首页';
        $this -> display();
    }


    /**消息管理**/
    public function notice(){

        $this -> meta_title = '消息管理';
        $this -> display('Main/notice/notice');
    }


    /**消息管理**/
    public function noticePost(){

        $this -> meta_title = '消息管理';
        $this -> display('Main/notice/noticePost');
    }


    /**系统设置**/
    public function sysSet(){

        $this -> meta_title = '系统设置';
        $this -> display('user/index');
    }


    /**推广专员信息管理**/
    public function msgList(){
        $dbStaff = M('Staff');
        $map = $this -> _queryTime();
        $staff_name = I('staff_name');
        if(is_numeric($staff_name)){
            $map["id|staff_name"] =   array(intval($staff_name),array('like','%'.$staff_name.'%'),'_multi'=>true);
        }else{
            $map['staff_name']    =   array('like', '%'.(string)$staff_name.'%');
        }
        $resStaff = $this -> lists($dbStaff,$map);
        int_to_string($resStaff);
        $this -> assign('resStaff',$resStaff);
        $this -> meta_title = '推广专员信息管理';
        $this -> display('Main/Msg/msgList');
    }


    /**导出推广专员信息**/
    public function msgOut(){
        $excel = A('Excel');
        $xlsCell = array(
            array('id', 'ID'),
            array('staff_name', '昵称'),
            array('staff_real', '真实姓名'),
            array('mobile', '手机号'),
            array('card_id', '身份证号'),
            array('referee', '推荐人'),
            array('game_id', '游戏ID'),
            array('money', '余额'),
            array('consume_coin', '消费币'),
            array('create_time', '注册时间'),
            array('status', '状态'),
        );
        $field = null;
        foreach ($xlsCell as $key => $value) {
            if($key == 0){
                $field = $value[0];
            }else{
                $field .= "," . $value[0];
            }
        }
        $xlsModel = M('Staff');
        if (IS_POST) {
            $map = $this -> _queryTime();
            $staff_name = I('staff_name');
            if(is_numeric($staff_name)){
                $map["id|staff_name"] =   array(intval($staff_name),array('like','%'.$staff_name.'%'),'_multi'=>true);
            }else{
                $map['staff_name']    =   array('like', '%'.(string)$staff_name.'%');
            }
            $end_time = $map['create_time'];
            if(empty($end_time) && empty($staff_name)){
                $xlsName = 'Staff全表导出';
                $xlsData = $xlsModel->Field($field)->order('id DESC')->select();
            }elseif(empty($end_time) && $staff_name){
                $xlsName = 'Staff表专员搜索结果导出';
                $where['id|staff_name'] = array(intval($staff_name), array('like', '%' . $staff_name . '%'), '_multi' => true);
                $xlsData = $xlsModel->Field($field)->where($where)->order('id DESC')->select();
            }else {
                $xlsName = 'Staff表搜索结果导出';
                $xlsData = $xlsModel->Field($field)->where($map)->order('id DESC')->select();
            }
        }
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['create_time'] = $v['create_time'] == null ? '-' : date("Y-m-d H:i",$v['create_time']);
            $xlsData[$k]['status'] = $v['status'] == 1 ? '正常' : '禁用';
        }
        $excel->exportExcel($xlsName,$xlsCell,$xlsData);
    }


    /**修改推广专员信息**/
    public function msgEdit(){
        $dbStaff = M('staff');
        $where['id'] = I('id');
        if(IS_POST){
            $data = array(
                'staff_name' => I('post.name'),
                'referee' => I('post.referee'),
                'mobile' => I('post.mobile'),
            );
            $resStaff = $dbStaff -> where($where) -> save($data);
            if($resStaff == 0){
                $this -> success('什么都没更改，正跳转至列表页...',U('Main/msgList'));
            }elseif($resStaff == 1){
                $this -> success('修改成功',U('Main/msgList'));
            }else{
                $this -> error('修改失败');
            }
        }
        $resStaffEdit = $dbStaff -> where($where) -> find();
        $this->assign('resStaffEdit', $resStaffEdit);
        $this -> meta_title = '修改推广专员信息';
        $this -> display('Main/msg/msgEdit');
    }


    /**添加推广专员**/
    public function msgAdd(){
        if(IS_POST) {
            $dbStaff = D('Staff');
            $data = array(
                'staff_name' => I('post.name'),
                'referee' => I('post.referee'),
                'mobile' => I('post.mobile')
            );
            if($dbStaff->msg_insert($data)){
                $this->success('增加成功', U('Main/msgList'));
            }else{
                $this->error($dbStaff->getError());
            }
        }else {
            $this->meta_title = '添加推广专员';
            $this->display('Main/msg/msgAdd');
        }
    }


    /**状态修改**/
    public function changeStatus($method=null,$dbname=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = in_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid($dbname, $map );
                break;
            case 'resumeuser':
                $this->resume($dbname, $map );
                break;
            case 'deleteuser':
                $this->delete($dbname, $map );
                break;
            default:
                $this->error('参数非法');
        }
    }


    /**关系管理**/
    public function relation(){

        $this -> meta_title = '关系管理';
        $this -> display('Main/relation/index');
    }


    /**接口基本设置**/
    public function apiSet(){

        $this -> meta_title = '接口基本设置';
        $this -> display('Main/apiSet/apiSet');
    }

    /**添加接口**/
    public function apiAdd(){

        $this -> meta_title = '添加接口';
        $this -> display('Main/apiSet/apiAdd');
    }


    /**任务列表**/
    public function taskList(){
        $dbTask = M('Task');
        $task_name = I('task_name');
        $start = strtotime(I('start_time'));
        $end = strtotime(I('end_time'));
        if($start || $end){
            if($start >= $end){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['create_time'] = array(array('gt', $start), array('lt', $end));
            }
        }
        if(is_numeric($task_name)){
            $map['id|name'] =   array(intval($task_name),array('like','%'.$task_name.'%'),'_multi'=>true);
        }else{
            $map['name']    =   array('like', '%'.(string)$task_name.'%');
        }
        $resTask = $this -> lists($dbTask,$map);
        int_to_string($resTask);
        $this -> assign('resTask',$resTask);
        $this -> meta_title = '任务列表';
        $this -> display('Main/task/taskList');
    }


    /**添加任务**/
    public function taskAdd(){
        $db_task = D('Task');
        if(IS_POST) {
            $data = array(
                'name' => I('name', '', 'htmlspecialchars'),
                'type' => I('post.type'),
                'inneed' => I('post.inneed'),
                'start_time' => strtotime(I('post.start_time')),
                'end_time' => strtotime(I('post.end_time')),
                'tasker' => session('user_auth')['username'],
                'status' => I('status'),
            );
            if($db_task -> task_insert($data)){
                $this -> success('增加成功', U('Main/taskList'));
            }else{
                $this -> error($db_task -> getError());
            }
        }else {
            $this -> meta_title = '添加任务';
            $this -> display('Main/task/taskAdd');
        }
    }


    /**任务发布记录**/
    public function taskPost(){
        $dbTask = M('Task');
        $task_name = I('task_name');
        $start = strtotime(I('start_time'));
        $end = strtotime(I('end_time'));
        if($start || $end){
            if($start >= $end){
                $this -> error('查询的开始日期大于结束日期，这让我很为难啊...');
            }else{
                $map['create_time'] = array(array('gt', $start), array('lt', $end));
            }
        }
        $map['status']  =   array('eq',1);
        if(is_numeric($task_name)){
            $map['id|name'] =   array(intval($task_name),array('like','%'.$task_name.'%'),'_multi'=>true);
        }else{
            $map['name']    =   array('like', '%'.(string)$task_name.'%');
        }
        $resTask = $this -> lists($dbTask,$map);
        int_to_string($resTask);
        $this -> assign('resTask',$resTask);
        $this -> meta_title = '任务发布记录';
        $this -> display('Main/task/taskPost');
    }


    /**分红管理**/
    public function cashGiven(){

        $this -> meta_title = '分红管理';
        $this -> display('Main/cash/cashGiven');
    }


    /**出入帐理**/
    public function cashIo(){

        $this -> meta_title = '出入帐理';
        $this -> display('Main/cash/cashIo');
    }


    /**财务总表**/
    public function cashTotal(){

        $this -> meta_title = '财务总表';
        $this -> display('Main/cash/cashTotal');
    }


    /**奖励明细**/
    public function cashDetail(){

        $this -> meta_title = '奖励明细';
        $this -> display('Main/cash/cashDetail');
    }


























}
