<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

class FeedController extends AdminController {

    /**反馈列表**/
    public function feed(){
        $db_msg = M('message');
        $db_admin = M('member');
        $where_msg['mid'] = 0;
        $where_msg['status'] = array('lt',3);
        $res_msg = $this -> lists($db_msg,$where_msg);
        foreach($res_msg as $k => $v){
            $where['admin_id'] = session('user_auth')['uid'];
            $res_nmsg = $db_admin->where($where)->find();
            $res_msg[$k]['admin_name'] = $res_nmsg['nickname'];
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

    /**删除反馈**/
    public function feedDel(){
        $db_msg = M('message');
        $where['id'] = I('id');
        $res = $db_msg -> where($where) ->limit('1') -> delete();
        if($res == 1){
            $this->success('删除成功', U('feed/feed'));
        }else{
            $this->error('删除失败');
        }
    }

    /**查看/回复反馈 - 判断**/
    public function feedRe(){
        $db_msg_re = M('message_reply');
        $db_msg = M('message');
        $where['id'] = I('id');
        $where_msg_re['msgid'] = I('id');
        $res_msg_detail = $db_msg -> where($where) -> find();
        $this->assign('res_msg_detail',$res_msg_detail);
        /**the following is the kernel code to show messages and replies(Admin)**/
        $where_msg['uid'] = $res_msg_detail['uid'];
        $where_msg['status'] = array('in','0,1,2,4') ;
        $where['mid'] = 0 ;
        $res_msg = $db_msg -> where($where_msg) ->where($where) -> select();
        foreach($res_msg as $k => $v) {
            $array[] = $res_msg[$k]['id'];
        }
        $where_msg['mid'] = array('in',$array);
        $where_re['msgid'] = array('in',$array);
        $where_re['status'] = array('in','0,1,2') ;
        $res_msg = $db_msg -> where($where_msg) -> order("create_time DESC") -> select();
        $res_re = $db_msg_re -> where($where_re) -> order("create_time DESC") -> select();
        /*分页
        $res_msg = $this -> lists($db_msg,$where_msg);
        $res_re = $this -> lists($db_msg_re,$where_re);
        */
        foreach ($res_msg as $k => $v) {
            $res_msg[$k]['msgid'] = 0;
            $res_msg[$k]['re_content'] = null;
            $res_msg[$k]['cid'] = I('id');
        }
        foreach ($res_re as $k => $v) {
            $res_re[$k]['mid'] = 0;
            $res_re[$k]['content'] = null;
            $res_re[$k]['cid'] = I('id');
        }
        $total = array_merge($res_msg,$res_re);
        array_multisort(array_column($total,'create_time'),SORT_ASC,$total);   //将合并的数组按照create_time升序排列
        $this->assign('total',$total);
        $this->meta_title = '反馈详情';
        $this->display('main/feed/feedRe');
    }

    /**回复反馈(提交按钮) - 动作**/
    public function feedReing(){
        $db_msg_re = M('message_reply');
        $db_msg = M('message');
        if(I('status') == 2 ){
            $this->error('此反馈已关闭，不可再回复。');
        }else{
            /**write into table message**/
            $date['aid'] = session('user_auth')['uid'];
            $date['reply_time'] = time();   //修改message表中的回复时间
            $date['status'] = 1 ;
            $where_msg['id'] = I('id');
            /**write into table message_reply**/
            $data['re_content'] = I('content');
            $data['msgid'] = I('id');
            $data['aid'] = session('user_auth')['uid'];
            $data['create_time'] = time();
            $data['status'] = 1 ;
            if(!empty($data['re_content'])){
                $db_msg -> data($date) -> where($where_msg) -> save();
                $db_msg_re -> data($data) -> add();
                $this->success('回复成功', U('feed/feed'));
            }else{
                $this->error('回复内容不能为空。');
            }
        }
    }

    /**关闭反馈**/
    public function feedEnd(){
        $db_re = M('message_reply');
        $db_msg = M('message');
        $where_re['msgid'] = I('id');
        $data_re['status'] = '2';
        $where_msg['id'] = I('id');
        $res_re = $db_re -> data($data_re) -> where($where_re) -> save();
        $res_msg = $db_msg -> data($data_re) -> where($where_msg) -> save();
        if($res_re && $res_msg){
            $this->success('关闭成功', U('feed/feed'));
        }else{
            $this->error('关闭失败了，请及时联系技术人员');
        }
    }

























}
