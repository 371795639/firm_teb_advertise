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


    public function index(){
        //Nothing need to do here.
    }


    /**推广专员信息管理及导出**/
    public function msgList($method = null){
        $dbStaff = M('Staff');
        $map = $this -> _queryTime();
        $staff_name = I('staff_name');
        $map['status'] = array('egt',0);
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
            $id['id'] = $_POST['id'];
            $data = array(
                'staff_name' => I('name'),
                'referee' => I('referee'),
                'mobile' => I('mobile'),
            );
            $resStaff = $dbStaff -> where($id) -> save($data);
            if($resStaff == 0){
                $this -> success('什么都没修改，跳转至列表页',U('Main/msgList'));
            }elseif($resStaff == 1){
                $this -> success('修改成功',U('Main/msgList'));
            }else{
                $this -> error('修改失败');
            }
        }else {
            $resStaffEdit = $dbStaff->where($where)->find();
            $this->assign('resStaffEdit', $resStaffEdit);
            $this->meta_title = '修改推广专员信息';
            $this->display('Main/msg/msgEdit');
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
        //Begin >>The following code can't be moved to other palce!
        $this -> assign('type',$type);
        $this -> assign('status',$status);
        //End<<
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
        if(empty($end_time) && empty($task_name) && empty($type) && empty($status)){
            $xlsName = 'Task全表导出';
        }elseif($task_name){
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
        $dbTask = D('Task');
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
            if($dbTask -> task_insert($data)){
                $this -> success('增加成功', U('Main/taskList'));
            }else{
                $this -> error($dbTask -> getError());
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
        $map['status']  = 2;
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

    /**编辑任务**/
    public function taskEdit(){
        $dbTask = M('Task');
        $where['id'] = I('id');
        if(IS_POST){
            $id['id'] = $_POST['id'];
            $data = array(
                'name' => I('name', '', 'htmlspecialchars'),
                'type' => I('post.type'),
                'inneed' => I('post.inneed'),
                'start_time' => strtotime(I('post.start_time')),
                'end_time' => strtotime(I('post.end_time')),
                'tasker' => session('user_auth')['username'],
                'status' => I('status'),
            );
            $resStaff = $dbTask -> where($id) -> data($data) -> save();
            if($resStaff == 0){
                $this -> success('什么都没更改，正跳转至列表页...',U('Main/taskList'));
            }elseif($resStaff == 1){
                $this -> success('修改成功',U('Main/taskList'));
            }else{
                $this -> error('修改失败');
            }
        }else {
            $resTask = $dbTask->where($where)->find();
            $this->assign('resTask', $resTask);
            $this->meta_title = '编辑任务';
            $this->display('Main/task/taskEdit');
        }
    }


    /**查看任务**/
    public function taskView(){
        $dbTask = M('Task');
        $where['id'] = I('id');
        $resTask = $dbTask->where($where)->find();
        $this->assign('resTask', $resTask);
        $this->meta_title = '编辑任务';
        $this->display('Main/task/taskView');
    }


    /**消息管理**/
    public function notice($method = null){
        $dbNotice = M('notice');
        $noticeType = M('notice_type');
        $map = $this -> _queryTime();
        $notice_name = I('notice_name');
        $notice_type = I('notice_type');
        $this -> assign('notice_type',$notice_type);
        if($notice_name) {
            if (is_numeric($notice_name)) {
                $map['id|notice_title'] = array(intval($notice_name), array('EQ', $notice_name));
            } else {
                $map['notice_title'] = array('like', '%' . $notice_name . '%');
            }
        }
        if($notice_type){
            $map['notice_type_id'] = $notice_type;
        }
        $resNotice = $this -> lists($dbNotice,$map);
        foreach ($resNotice as $k => $v) {
            if(strlen($resNotice[$k]['notice_content']) < 100){
                $resNotice[$k]['n_content'] = $resNotice[$k]['notice_content'];
            }else{
                $resNotice[$k]['n_content'] = msubstr($resNotice[$k]['notice_content'],0,80);
            }
            $id['id'] =$resNotice[$k]['notice_type_id'];
            $type = $noticeType -> where($id) -> find();
            $resNotice[$k]['notice_type_name'] = $type['notice_type_name'];
        }
        $type = $noticeType -> select();
        $this -> assign('type',$type);
        $this -> assign('resNotice',$resNotice);
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        $xlsCell = array(
            array('id', 'ID'),
            array('notice_title', '公告主题'),
            array('create_ip', 'IP'),
            array('notice_content', '功能内容'),
            array('notice_type_id', '公告类型'),
            array('img_url', '图片地址'),
            array('create_time', '创建时间'),
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
        if(empty($end_time) && empty($notice_name) && empty($notice_type)){
            $xlsName = 'Notice全表导出';
        }elseif($notice_name){
            $xlsName = 'Notice表通知主题搜索结果导出';
        }else {
            $xlsName = 'Notice表搜索结果导出';
        }
        $xlsData = $dbNotice->Field($field)->where($map)->order('id DESC')->select();
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['create_time'] = $v['create_time'] == 0 ? '-' : date("Y-m-d H:i",$v['create_time']);
            $xlsData[$k]['status']      = $v['status']      == 1 ? '正常' : '禁用';
        }
        switch (strtolower($method)){
            case 'list':
                //nothing need to do here!
                break;
            case 'out':
                $excel->exportExcel($xlsName,$xlsCell,$xlsData);
                break;
        }
        $this -> meta_title = '消息管理';
        $this -> display('Main/notice/notice');
    }


    /**发布消息**/
    public function noticePost(){
        if($_POST)
        {
            //提交公告内容信息
            if($_POST['notice_type']>0&&$_POST['notice_title']&&$_POST['content']){
                $notice = D('notice');
                $data['create_ip'] = $_SERVER['REMOTE_ADDR'];
                $data['aid'] = session('user_auth')['username'];
                $data['notice_content'] = $_POST['content'];
                $data['notice_type_id'] = $_POST['notice_type'];
                $data['notice_title'] = $_POST['notice_title'];
                //上传图片信息
                $upload = new \Think\Upload();
                $upload->maxSize   =     2*1024*1024 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =     './Uploads/Picture/'; // 设置附件上传根目录
                $info   =   $upload->uploadOne($_FILES['photo1']);//上传文件信息
                $savepath = $info['savepath'];//存放图片的文件夹名
                $savename = $info['savename'];//图片名
                $img_url = 'http://'.$_SERVER['HTTP_HOST'].'/Uploads/Picture/'.$savepath.$savename;//拼接图片地址
                $data['img_url'] = $img_url;//存放图片路径
                if($info&&$notice->add($data)){
                    $this -> success('提交成功',U('Main/notice'));
                }elseif(empty($info)){
                    $this -> error('请选择图片再提交');
                }else{
                    $this -> error('提交失败');
                }
            }
            else{
                echo "<script>alert('请正确输入信息');history.go(-1); </script>";
            }
        }else {
            $result = D('notice_type')->select();
            $this->assign('type', $result);
            $this->meta_title = '消息管理';
            $this->display('Main/notice/noticePost');
        }
    }


    /**修改消息**/
    public function noticeEdit()
    {
        $dbNotice = M('notice');
        $dbNoticeType = M('notice_type');
        $noticeID = I('id');
        $resNotice = $dbNotice -> where(array('id='.$noticeID))->find();
        $resNoticeType = $dbNoticeType -> where(array('id='. $resNotice['notice_type_id']))->find();//$resNoticeType['notice_type_name']消息类型
        $resNoticeAllType = $dbNoticeType -> select();
        if(IS_POST)
        {
            header('Content-Type:text/html;charset=utf-8');
            $noticeTypeName = $_POST['noticeType'];
            $resNoticeTypeData = $dbNoticeType -> where(array('notice_type_name' => $noticeTypeName))->find();
            $resNoticeTypeID = $resNoticeTypeData['id'];
            $data = array(
                'id' => I('post.noticeID'), //消息id
                'notice_title' => I('post.noticeTitle'),//消息标题
                'notice_content'=> I('post.noticeContent'),//消息内容
            );
            $data['create_time'] = time();
            $data['create_ip'] = $_SERVER['REMOTE_ADDR'];
            $data['notice_type_id'] = $resNoticeTypeID;
            //图片信息
            $upload = new \Think\Upload();
            $upload->maxSize   =     2*1024*1024 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     './Uploads/Picture/'; // 设置附件上传根目录
            $info   =   $upload->uploadOne($_FILES['photo']);//上传文件信息
            //$info   =   $upload->uploadOne($_FILES['photo']);//上传文件信息
            $savepath = $info['savepath'];//存放图片的文件夹名
            $savename = $info['savename'];//图片名
            $img_url = 'http://'.$_SERVER['HTTP_HOST'].'/Uploads/Picture/'.$savepath.$savename;//拼接图片地址
            $data['img_url'] = $img_url;//存放图片路径
            if($dbNotice->where(array('id='.$data['id']))->save($data)){
                $this -> success('修改成功',U('Main/notice'));
            }else{
                $this -> error('修改失败',U('Main/notice'));
            }
        }
        $this -> assign('noticeID',$resNotice);
        $this -> assign('noticeTitle',$resNotice);
        $this -> assign('noticeContent',$resNotice);
        $this -> assign('noticeType',$resNoticeType);
        $this -> assign('noticeAllType', $resNoticeAllType);
        $this -> meta_title = '修改消息信息';
        $this -> display('Main/Notice/noticeEdit');
    }

    /**删除消息**/
    public function noticeDelete()
    {
        $notice = D('notice');
        $noticeDeleteId = I('id');//获取删除的ID
        $notice->where('id='.$noticeDeleteId)->delete(); // 删除id为5的用户数据
        $this -> success('删除成功',U('Main/notice'));
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


    /**系统设置**/
    public function sysSet(){

        $this -> meta_title = '系统设置';
        $this -> display('user/index');
    }
}
