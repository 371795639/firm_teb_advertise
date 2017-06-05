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
        $this -> display('main/notice/notice');
    }

    /**消息管理**/
    public function notice_post(){

        $this -> meta_title = '消息管理';
        $this -> display('main/notice/notice_post');
    }

    /**系统设置**/
    public function sys_set(){

        $this -> meta_title = '系统设置';
        $this -> display('user/index');
    }

    /**推广专员信息管理**/
    public function msg_list(){
        $db_staff = M('Staff');
        $res_staff = $this->lists($db_staff);
        $this -> assign('res_staff',$res_staff);
        $this -> meta_title = '推广专员信息管理';
        $this -> display('main/msg/msg_list');
    }

    /**修改推广专员信息**/
    public function msg_edit(){
        $db_staff = D('staff');
        $res_staff_edit = $db_staff -> getStaffById(I('get.id',0));
        if($res_staff_edit){
            $this -> success('修改成功','main/mag_list');
        }else{
            $this -> success('修改失败');
        }
        $this -> assign('res_staff_edit',$res_staff_edit);
        $this -> meta_title = '修改推广专员信息';
        $this -> display('main/msg/msg_edit');
    }

    /**删除推广专员信息**/
    public function msg_del(){
        $db_staff = D('staff');
        $res_staff_edit = $db_staff -> getStaffById(I('get.id',0));

    }

    /**添加推广专员**/
    public function msg_add(){
        //session_start();
        $db_staff = M('staff');
        $data['staff_name'] = I('post.name','','htmlspecialchars');
        $data['referee'] = I('post.referee');
        $data['mobile'] = I('post.mobile');
        $data['create_time'] = time();
        $data['status'] = 1;
        /*
        $res_staff = $db_staff->add($data);
        if($res_staff){
            $this -> success('添加成功','main/msg_add');
        }else {
            $this -> success('添加失败');
        }
        */

        /*
        $session_id = empty($_SESSION['session_id']) ? -1 : $_SESSION['session_id'];
        $post_id = empty($_POST['post_id']) ? -2 : $_POST['post_id'];
        if($session_id !== $post_id){
            if($post_id !== -2){
                $_SESSION['session_id'] = $post_id;
                $res_staff = $db_staff->add($data);
                if($res_staff){
                    $this -> success('添加成功','admin/main/msg_add');
                }else{
                    $this -> success('添加失败');
                }
            }
        }
        */
        $this -> meta_title = '添加推广专员';
        $this -> display('main/msg/msg_add');
    }

    public function msg_adding(){
        /*
        $data['staff_name'] = I('post.name','','htmlspecialchars');
        p($data);
        */


    }


    /**关系管理**/
    public function relation(){

        $this -> meta_title = '关系管理';
        $this -> display('main/relation/index');
    }

    /**接口基本设置**/
    public function api_set(){

        $this -> meta_title = '接口基本设置';
        $this -> display('main/api_set/api_set');
    }

    /**添加接口**/
    public function api_add(){

        $this -> meta_title = '添加接口';
        $this -> display('main/api_set/api_add');
    }

    /**任务列表**/
    public function task_list(){

        $this -> meta_title = '消息管理';
        $this -> display('main/task/task_list');
    }

    /**任务发布记录**/
    public function task_post(){

        $this -> meta_title = '任务发布记录';
        $this -> display('main/task/task_post');
    }

    /**分红管理**/
    public function cash_given(){

        $this -> meta_title = '分红管理';
        $this -> display('main/cash/cash_given');
    }

    /**出入帐理**/
    public function cash_io(){

        $this -> meta_title = '出入帐理';
        $this -> display('main/cash/cash_io');
    }

    /**财务总表**/
    public function cash_total(){

        $this -> meta_title = '财务总表';
        $this -> display('main/cash/cash_total');
    }

    /**奖励明细**/
    public function cash_detail(){

        $this -> meta_title = '奖励明细';
        $this -> display('main/cash/cash_detail');
    }


























}
