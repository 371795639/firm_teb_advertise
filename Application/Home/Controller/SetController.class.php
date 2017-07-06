<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/6/29
 * Time: 17:10
 */
namespace Home\Controller;
use User\Api\UserApi;

class SetController extends HomeController{

    /**我的记录**/
    public function set(){
        $dbStaff  = D('staff');
        $user_id   = $_SESSION['userid'];
        $resStaff = $dbStaff->where('id='.$user_id)->find();
        $this->assign('staff',$resStaff);
        $this->display('set');
    }

    /**修改登录密码**/
    public function setLoginPsd(){
        $dbStaff  = D('staff');
        $user_id   = $_SESSION['userid'];
        $old_pwd = md5(I('old_pwd'));
        $new_pwd = md5(I('new_pwd'));
        $mobile = $dbStaff->where(array('id'=>$user_id))->getField('mobile');
        $is_set = $dbStaff->where(array('id'=>$user_id,'staff_pwd'=>$old_pwd))->find();
        //验证码
        $verify = $_SESSION['verifyNum']['content'];
        if (IS_POST){
            if(I('test') != $verify){
                $data['code'] = 2;
            }elseif (empty($is_set)){
                $data['code'] = 3;
            }else{
                $update_data = $dbStaff->where(array('id'=>$user_id))->save(array('staff_pwd'=>$new_pwd));
                if($update_data){
                    $data['code'] = 1;
                }else{
                    $data['code'] = 4;
                }
            }
            $this->ajaxReturn($data,"JSON");
        }
        $this->assign('mobile',$mobile);
        $this->display('setLoginPsd');
    }
}