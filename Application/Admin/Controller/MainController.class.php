<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

class MainController extends AdminController {

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
        $staff_name       =   I('staff_name');
        $map['status']  =   array('egt',0);
        if(is_numeric($staff_name)){
            $map['id|staff_name']=   array(intval($staff_name),array('like','%'.$staff_name.'%'),'_multi'=>true);
        }else{
            $map['staff_name']    =   array('like', '%'.(string)$staff_name.'%');
        }
        $resStaff = $this -> lists($dbStaff,$map);
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
        }else {
            $resStaffEdit = $dbStaff -> where($where) -> find();
            $this->assign('resStaffEdit', $resStaffEdit);
            $this -> meta_title = '修改推广专员信息';
            $this -> display('Main/msg/msgEdit');
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
                //'tasker' => session('userAuth')['username']
            );
            if($db_task -> task_insert($data)){
                $this -> success('增加成功', U('Main/taskPost'));
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
        $task_name       =   I('task_name');
//        $map['status']  =   array('egt',0);
        if(is_numeric($task_name)){
            $map['id|name']=   array(intval($task_name),array('like','%'.$task_name.'%'),'_multi'=>true);
        }else{
            $map['name']    =   array('like', '%'.(string)$task_name.'%');
        }
//        $resTask = $this -> lists($dbTask,$map);
        $resTask = $this -> lists($dbTask);
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
