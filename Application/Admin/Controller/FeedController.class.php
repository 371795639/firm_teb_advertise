<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

class FeedController extends AdminController {

    /**反馈列表**/
    public function feed(){
        $db_msg     = M('message');
        $res_msg    = $this -> lists($db_msg);
        foreach($res_msg as $k => $v){
            if(strlen($res_msg[$k]['content']) < 80){
                $res_msg[$k]['n_content'] = $res_msg[$k]['content'];
            }else{
                $res_msg[$k]['n_content'] = msubstr($res_msg[$k]['content'],0,30);
            }
        }
        $this->assign('msg', $res_msg);
        $this->meta_title = '反馈列表';
        $this->display('main/feed/index');
    }


    /**查看反馈**/
    public function feedView(){
        $msg = D('Message');
        $msgRe  = D('MessageReply');
        $id = I('id');
        $resMsgDetail   = $msg -> get_msg_by_id($id);
        $resMsgRe       = $msgRe -> get_reply_by_msgid($id);
        $this -> assign('resMsgRe',$resMsgRe);
        $this -> assign('resMsgDetail',$resMsgDetail);
        $this -> meta_title = '反馈详情';
        $this -> display('main/feed/feedView');
    }


    /**回复反馈**/
    public function feedReply(){
        $msg    = D('Message');
        $msgRe  = D('MessageReply');
        $id     = I('id');
        $status = I('status');
        if($status == 2){
            $this -> error('此回复已关闭，不可再进行回复了哦。');
        }else{
            $reContent = I('reContent');
            if(empty($reContent)){
                $this -> error('回复内容不可为空！');
            }else{
                $data = array(
                    'reply_time'    => date('Y-m-d H:i:s'),
                    'admin'         => session('user_auth')['username'],
                    'status'        => 2,
                );
                $resMsg = $msg -> save_msg_by_id($id,$data);
                $date = array(
                    're_content'    => $reContent,
                    'msg_id'         => $id,
                    'admin'         => session('user_auth')['username'],
                    'create_time'   => date('Y-m-d H:i:s'),
                    'status'        => 2,
                );
                $resMsgReply = $msgRe -> add($date);
                if($resMsg && $resMsgReply){
                    $this -> success('回复成功',U('Feed/feed'));
                }else{
                    $this -> error('回复失败，系统出错，及时练习技术人员');
                }
            }
        }
    }


    /**删除反馈**/ //未在后台开启
    public function feedDel(){
        $dbMsg = D('Message');
        $where = I('id');
        $res = $dbMsg -> del_reply_by_id($where);
        if($res){
            $this->success('删除成功', U('feed/feed'));
        }else{
            $this->error('删除失败');
        }
    }

}
