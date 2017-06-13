<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Think\Controller;

class MainController extends AdminController {

    /**提取起止日期**/
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


    /**推广专员信息管理及导出**/
    public function msgList($method = null){
        $dbStaff = M('Staff');
        $map = $this -> _queryTime();
        $staff_name = I('staff_name');
        if($staff_name) {
            if (is_numeric($staff_name)) {
                $map["id|staff_name"] = array(intval($staff_name), array('like', '%' . $staff_name . '%'), '_multi' => true);
            } else {
                $map['staff_name'] = array('like', '%' . (string)$staff_name . '%');
            }
        }
        $resStaff = $this -> lists($dbStaff);
        //导出查询到的数据（不含分页）
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
        $end_time = $map['create_time'];
        if(empty($end_time) && empty($staff_name)){
            $xlsName = 'Staff全表导出';
        }elseif(empty($end_time) && $staff_name){
            $xlsName = 'Staff表专员搜索结果导出';
        }else {
            $xlsName = 'Staff表搜索结果导出';
        }
        $xlsData = $dbStaff->Field($field)->where($map)->order('id DESC')->select();
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['create_time'] = $v['create_time'] == 0 ? '-' : date("Y-m-d H:i",$v['create_time']);
            $xlsData[$k]['status']      = $v['status']      == 1 ? '正常' : '禁用';
        }
        switch (strtolower($method)){
            case 'list':
                $resStaff = $this -> lists($dbStaff,$map);
                break;
            case 'out':
                $excel->exportExcel($xlsName,$xlsCell,$xlsData);
                break;
        }
        int_to_string($resStaff);
        $this -> assign('resStaff',$resStaff);
        $this -> meta_title = '推广专员信息管理';
        $this -> display('Main/Msg/msgList');
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


    /**任务列表及导出**/
    public function taskList($method=null){
        $dbTask = M('Task');
        $map = $this -> _queryTime();
        $task_name = I('task_name');
        $type = I('type');
        if($type) {
            $map['type'] = $type;
        }
        $status = I('status');
        if($status) {
            $map['status'] = $status;
        }
        //The following code can't be moved to other palce!
        $this -> assign('type',$type);
        $this -> assign('status',$status);
        if($task_name) {
            if (is_numeric($task_name)) {
                $map['id|name'] = array(intval($task_name), array('like', '%' . $task_name . '%'), '_multi' => true);
            } else {
                $map['name'] = array('like', '%' . (string)$task_name . '%');
            }
        }
        $resTask = $this -> lists($dbTask);
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        $xlsCell = array(
            array('id', 'ID'),
            array('name', '任务名称'),
            array('type', '任务类型'),
            array('inneed', '任务指标'),
            array('start_time', '开始时间'),
            array('end_time', '结束时间'),
            array('create_time', '注册时间'),
            array('tasker', '发布者'),
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
        $end_time = $map['create_time'];
        if(empty($end_time) && empty($staff_name)){
            $xlsName = 'Task全表导出';
        }elseif(empty($end_time) && $staff_name){
            $xlsName = 'Task表专员搜索结果导出';
        }else {
            $xlsName = 'Task表搜索结果导出';
        }
        $status = array('-','未发布','进行中','已过期');
        $type = array('-','基本任务','额外任务');
        $xlsData = $dbTask->Field($field)->where($map)->order('id DESC')->select();
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['start_time']  = $v['start_time']  == 0 ? '-' : date("Y-m-d H:i",$v['start_time']);
            $xlsData[$k]['end_time']    = $v['end_time']    == 0 ? '-' : date("Y-m-d H:i",$v['end_time']);
            $xlsData[$k]['create_time'] = $v['create_time'] == 0 ? '-' : date("Y-m-d H:i",$v['create_time']);
            $xlsData[$k]['tasker']      = $v['tasker']      =='' ? '-' : $v['tasker'];
            $xlsData[$k]['status']      = $status[$v['status']];
            $xlsData[$k]['type']        = $type[$v['type']];
        }
        switch (strtolower($method)){
            case 'list':
                $resTask = $this -> lists($dbTask,$map);
                break;
            case 'out':
                echo $dbTask -> _sql();
                $excel->exportExcel($xlsName,$xlsCell,$xlsData);
                break;
        }
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
        $map = $this -> _queryTime();
        $task_name = I('task_name');
        $map['status']  = 1;
        $type = I('type');
        if($type) {
            $map['type'] = $type;
        }
        $this -> assign('type',$type);
        if($task_name) {
            if (is_numeric($task_name)) {
                $map['id|name'] = array(intval($task_name), array('like', '%' . $task_name . '%'), '_multi' => true);
            } else {
                $map['name'] = array('like', '%' . (string)$task_name . '%');
            }
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
