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
        $dbNotice = D('notice');
        $start = strtotime(I('time_start'));
        $end = strtotime(I('time_end'));
        $notice_name = I('notice_name');
        $notice_type = I('notice_type');
        //时间查询
        if($start || $end ){
            if($start > $end){
                $this -> error('开始时间不能大于结束时间');
            }else{
                $map['create_time'] = array('between',array("$start","$end"));//构造查询条件
            }
<<<<<<< .mine
||||||| .r1
        }
        $end_time = $map['create_time'];
        if(empty($end_time) && empty($staff_name)){
            $xlsName = 'Staff全表导出';
        }elseif(empty($end_time) && $staff_name){
            $xlsName = 'Staff表专员搜索结果导出';
        }else {
            $xlsName = 'Staff表搜索结果导出';
        }
        $xlsData = $dbStaff -> get_all_msg($field,$map);
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
        $dbStaff = D('staff');
        $where = I('id');
        if(IS_POST){
            $id = $_POST['id'];
            $data = array(
                'staff_name' => I('name'),
                'referee'    => I('referee'),
                'mobile'     => I('mobile'),
            );
            $resStaff = $dbStaff -> msg_save($id,$data);
            if($resStaff == 0){
                $this -> success('什么都没修改，正跳转至列表页...',U('Main/msgList'));
            }elseif($resStaff == 1){
                $this -> success('修改成功',U('Main/msgList'));
            }else{
                $this -> error('修改失败');
            }
        }else {
            $resStaffEdit = $dbStaff -> msg_find($where);
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
        $id = in_array($id) ? implode(',',$id) : $id;
        if ( empty($id) || $id[0] == 0) {
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
            case 'ipost':
                $abc = $this -> a = $id;
                $bc['id'] = array('in',$abc);
                return $bc;
                break;
            default:
                $this->error('参数非法');
        }
    }


    /**任务列表及导出**/
    public function taskList($method=null){
        $dbTask = D('Task');
        $timeNew = A('Cash');
        $map = $timeNew -> _queryCreateTime();
        $task_name = I('task_name');
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
        $resTask = $this -> lists($dbTask);
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        $xlsCell = array(
            array('id', 'ID'),
            array('name', '任务名称'),
            array('isgame', '是否游戏任务'),
            array('type', '任务类型'),
            array('inneed', '任务指标'),
            array('create_time', '创建时间'),
            array('money', '任务金额'),
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
        $type = array('-','基本任务','额外任务');
        $xlsData = $dbTask -> get_all_task($field,$map);
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['create_time'] = $v['create_time'] == 0 ? '-' : $v['create_time'];
            $xlsData[$k]['isgame']      = $v['isgame']      == 0 ? '是': '否';
            $xlsData[$k]['tasker']      = $v['tasker']      =='' ? '-' : $v['tasker'];
            $xlsData[$k]['type']        = $type[$v['type']];
            $xlsData[$k]['status']      = $v['status']      == 0 ? '无效' : '有效';
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
                'name'   => I('name', '', 'htmlspecialchars'),
                'type'   => I('post.type'),
                'inneed' => I('post.inneed'),
                'money'  => I('post.money'),
                'tasker' => session('user_auth')['username'],
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


    /**发布周任务**/
    public function taskPost($abc = null){
        $dbTask = D('Task');
        $dbTaskWeekly = M('TaskWeekly');
        date_default_timezone_set('PRC');
        // Begin: the 3 line codes below are for getting next Monday 2:00 am and next Sunday 11:59:59 pm
        $start_time = date('Y-m-d 02:00:00',strtotime('Monday'));  //TODO 待定
        $ss = strtotime($start_time);
        $end_time = date('Y-m-d 11:59:59',strtotime('Sunday',$ss));
        $data = array(
            'start_time'=> $start_time,
            'end_time'  => $end_time,
            'post_time' => date('Y-m-d H:i:s'),
            'tasker'    => session('user_auth')['username'],
            'status'    => '3',
        );
        $date['status'] = 1;
        if($abc == 2) {
            $task_id = $this -> changeStatus('ipost', null);
            $resTask = $dbTask -> where($task_id) -> select(); //数组查询结果
            foreach($resTask as $k => $v){
                $new = $resTask[$k]['id'];
                $data['task_id']= $resTask[$k]['id'];
                $data['name']   = $resTask[$k]['name'];
                if($resTask[$k]['status'] == 0){    //避免重复发布
                    $resTaskWeekly  = $dbTaskWeekly -> add($data);
                    $result = $dbTask -> save_task_by_id($new, $date);
                }
            }
        }else {
            $task_id = I('id');
            $resTask = $dbTask -> get_task_by_id($task_id);
            $data['task_id']    = $task_id;
            $data['name']       = $resTask['name'];
            if($resTask['status'] == 0) {       //避免重复发布
                $resTaskWeekly = $dbTaskWeekly -> add($data);
                $result = $dbTask -> save_task_by_id($task_id, $date);
            }
        }
        if($resTaskWeekly && $result){
            $this -> success('发布成功',U('Main/taskList'));
=======
        }
        $end_time = $map['create_time'];
        if(empty($end_time) && empty($staff_name)){
            $xlsName = 'Staff全表导出';
        }elseif(empty($end_time) && $staff_name){
            $xlsName = 'Staff表专员搜索结果导出';
        }else {
            $xlsName = 'Staff表搜索结果导出';
        }
        $xlsData = $dbStaff -> get_all_msg($field,$map);
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
        $dbStaff = D('staff');
        $where = I('id');
        if(IS_POST){
            $id = $_POST['id'];
            $data = array(
                'staff_name' => I('name'),
                'referee'    => I('referee'),
                'mobile'     => I('mobile'),
            );
            $resStaff = $dbStaff -> msg_save($id,$data);
            if($resStaff == 0){
                $this -> success('什么都没修改，正跳转至列表页...',U('Main/msgList'));
            }elseif($resStaff == 1){
                $this -> success('修改成功',U('Main/msgList'));
            }else{
                $this -> error('修改失败');
            }
        }else {
            $resStaffEdit = $dbStaff -> msg_find($where);
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
        $id = in_array($id) ? implode(',',$id) : $id;
        if ( empty($id) || $id[0] == 0) {
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
            case 'ipost':
                $abc = $this -> a = $id;
                $bc['id'] = array('in',$abc);
                return $bc;
                break;
            default:
                $this->error('参数非法');
        }
    }


    /**任务列表及导出**/
    public function taskList($method=null){
        $dbTask = D('Task');
        $timeNew = A('Cash');
        $map = $timeNew -> _queryCreateTime();
        $task_name = I('task_name');
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
        $resTask = $this -> lists($dbTask);
        //导出查询到的数据（不含分页）
        $excel = A('Excel');
        $xlsCell = array(
            array('id', 'ID'),
            array('name', '任务名称'),
            array('isgame', '是否游戏任务'),
            array('type', '任务类型'),
            array('inneed', '任务指标'),
            array('create_time', '创建时间'),
            array('money', '任务金额'),
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
        $type = array('-','基本任务','额外任务');
        $xlsData = $dbTask -> get_all_task($field,$map);
        foreach ($xlsData as $k => $v) {
            $xlsData[$k]['create_time'] = $v['create_time'] == 0 ? '-' : $v['create_time'];
            $xlsData[$k]['isgame']      = $v['isgame']      == 0 ? '是': '否';
            $xlsData[$k]['tasker']      = $v['tasker']      =='' ? '-' : $v['tasker'];
            $xlsData[$k]['type']        = $type[$v['type']];
            $xlsData[$k]['status']      = $v['status']      == 0 ? '无效' : '有效';
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
                'name'   => I('name', '', 'htmlspecialchars'),
                'type'   => I('post.type'),
                'inneed' => I('post.inneed'),
                'money'  => I('post.money'),
                'tasker' => session('user_auth')['username'],
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


    /**发布周任务**/
    public function taskPost($abc = null){
        $dbTask = D('Task');
        $dbTaskWeekly = M('TaskWeekly');
        date_default_timezone_set('PRC');
        // Begin: the 3 line codes below are for getting next Monday 2:00 am and next Sunday 23:59:59
        $start_time = date('Y-m-d 02:00:00',strtotime('Monday'));  //TODO 待定
        $ss = strtotime($start_time);
        $end_time = date('Y-m-d 23:59:59',strtotime('Sunday',$ss));
        $data = array(
            'start_time'=> $start_time,
            'end_time'  => $end_time,
            'post_time' => date('Y-m-d H:i:s'),
            'tasker'    => session('user_auth')['username'],
            'status'    => '3',
        );
        $date['status'] = 1;
        if($abc == 2) {
            $task_id = $this -> changeStatus('ipost', null);
            $resTask = $dbTask -> where($task_id) -> select(); //数组查询结果
            foreach($resTask as $k => $v){
                $new = $resTask[$k]['id'];
                $data['task_id']= $resTask[$k]['id'];
                $data['name']   = $resTask[$k]['name'];
                $data['type']   = $resTask[$k]['type'];
                if($resTask[$k]['status'] == 0){    //避免重复发布
                    $resTaskWeekly  = $dbTaskWeekly -> add($data);
                    $result = $dbTask -> save_task_by_id($new, $date);
                }
            }
        }else {
            $task_id = I('id');
            $resTask = $dbTask -> get_task_by_id($task_id);
            $data['task_id']    = $task_id;
            $data['name']       = $resTask['name'];
            if($resTask['status'] == 0) {       //避免重复发布
                $resTaskWeekly = $dbTaskWeekly -> add($data);
                $result = $dbTask -> save_task_by_id($task_id, $date);
            }
        }
        if($resTaskWeekly && $result){
            $this -> success('发布成功',U('Main/taskList'));
>>>>>>> .r4
        }else{
            $map = null;

        }
        //ID或者消息主题查询
        if(is_numeric($notice_name)){
            $map['id'] =   array(intval($notice_name),array('EQ',$notice_name));
        }else{
            $map['notice_title']    =   array('like', '%'.$notice_name.'%');
        }

<<<<<<< .mine
        //公告类型查询
||||||| .r1

    /**已完成任务**/
    public function taskDone(){

        $this->meta_title = '已完成任务';
        $this->display('Main/task/taskDone');
    }


    /**消息管理**/
    public function notice($method = null){
        $dbNotice = D('notice');
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
=======

    /**已完成任务**/
    public function taskDone(){
        $dbTask     = D('Task');
        $dbTaskDone = D("TaskDone");
        $uid = I('uid');
        if($uid) {
            $map['id|uid|task_id'] = $uid;
        }
        $resTaskDone = $this -> lists($dbTaskDone,$map);
        foreach($resTaskDone as $k => $v){
            $task_id = $resTaskDone[$k]['task_id'];
            $resTask = $dbTask -> get_task_by_id($task_id);
            $resTaskDone[$k]['name']    = $resTask['name'];
            $resTaskDone[$k]['money']   = $resTask['money'];
            $resTaskDone[$k]['type']    = $resTask['type'];
        }
        $this->assign('resTaskDone',$resTaskDone);
        $this->meta_title = '已完成任务';
        $this->display('Main/task/taskDone');
    }


    /**查看已完成任务**/
    public function taskDoneView(){
        $dbTask     = D('Task');
        $dbStaff    = D('Staff');
        $dbTaskDone = D("TaskDone");
        $id = I('id');
        $resTaskDone = $dbTaskDone -> get_done_by_id($id);
        $resStaff = $dbStaff -> msg_find($resTaskDone['uid']);
        $staff = array(
            'staff_name'    => $resStaff['staff_name'],
            'staff_real'    => $resStaff['staff_real'],
            'mobile'        => $resStaff['mobile'],
            'game_id'       => $resStaff['game_id'],
        );
        $resTask = $dbTask -> get_task_by_id($resTaskDone['task_id']);
        $this->assign('resTaskDone',$resTaskDone);
        $this->assign('staff',$staff);
        $this->assign('resTask',$resTask);
        $this->meta_title = '查看已完成任务';
        $this->display('Main/task/taskDoneView');

    }


    /**消息管理**/
    public function notice($method = null){
        $dbNotice = D('notice');
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
>>>>>>> .r4
        if($notice_type){
            $map['notice_type_id'] = array('EQ',$notice_type);
        }

        //调用查询分页方法lists
        $resNotice = $this -> lists($dbNotice,$map);
        int_to_string($resNotice);

        foreach ($resNotice as $key=> $value )
        {
            $noticeData[$key]['id'] = $value['id'];
            $noticeData[$key]['create_time'] = $value['create_time'];
            $noticeData[$key]['create_ip'] = $value['create_ip'];
            $noticeData[$key]['notice_title'] = $value['notice_title'];
            $noticeData[$key]['notice_content'] = $value['notice_content'];
            $noticeData[$key]['img_url'] = $value['img_url'];
            $noticeType = M("notice_type");
            $notice_type_id = $value['notice_type_id'];
            $noticeData[$key]['type'] = $noticeType->where(array('id'=>$notice_type_id))->find();
        }
        $result = D('notice_type')->select();
        $this->assign('type',$result);
        $this->assign('notice',$noticeData);
        $this -> meta_title = '消息管理';
        $this -> display('Main/notice/notice');
    }

    /**消息管理**/
    public function noticePost(){

        if($_POST)
        {
            //提交公告内容信息
            if($_POST['notice_type']>0&&$_POST['notice_title']&&$_POST['content']){
                $notice = D('notice');
                $maxid = $notice->max('id');
                $data['id'] = $maxid+1;
                $data['create_time'] = time();
                $data['create_ip'] = $_SERVER['REMOTE_ADDR'];
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
                    echo "<script>alert('提交成功');history.go(-1); </script>";

                }
                else{
                    echo "<script>alert('提交失败');history.go(-1); </script>";
                }
            }
            else{
                echo "<script>alert('请正确输入信息');history.go(-1); </script>";
            }

        }

        $result = D('notice_type')->select();
        $this->assign('type',$result);
        $this -> meta_title = '消息管理';
        $this -> display('Main/notice/noticePost');
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
//                echo "<script> if(alert('修改成功')) { window.location.reload()} </script>";
            }else{
//                echo "<script> if(alert('修改失败')) { window.location.reload()}</script>";
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
