<?php

namespace Home\Controller;
use Think\Controller;

class FeedController extends HomeController {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    /**
     * daili_message（反馈表）中status字段说明
     *状态为0：等待客服处理
     *状态为1：客服正在处理中，客户回复信息将记录在第一条反馈信息之下
     *状态为2：客服处理完成，客户反馈，将成为新的反馈信息
     *状态为4：客户针对当前反馈的回复信息
     * 不喜欢3，所以状态只有0,1,2,4
     */

    /**
     * daili_message_reply（回复表）中status字段说明
     *状态为0：未处理
     *状态为1：正在处理中
     *状态为2：处理完成
     */
    public function index(){
        //nothing need to do here.
        //display不用传参，自动加载Feed文件夹下模板文件
        $this->display();
    }

    /**意见反馈**/
    public function refeedbk(){
        $db_msg = M('daili_message');
        $data['uid'] = $_SESSION['uid'];
        $data['content'] = I('content');
        $data['create_time'] = time();
        if(!empty($data['content'])){
            $db_msg->data($data)->add();
        }
        $this->assign('data',$data);
        $this->display();
    }


    /**加载继续反馈页面**/
    public function feedbker(){
        $data['mid'] = I('id');
        $data['aid'] = I('aid');
        $this->assign('data',$data);
        $this->display();
    }


    /**继续反馈动作**/
    public function refeedbkerr(){
        $db_msg = M('daili_message');
        $data['content'] = I('content');
        $data['create_time'] = time();
        $data['status'] = 4;
        $data['uid'] = $_SESSION['uid'];
        $data['mid'] = I('mid');
        $data['aid'] = I('aid');
        if(!empty($data['content'])){
            $db_msg->data($data)->add();
            $this->redirect('home/feed/refeedbkList','回复成功，跳转中...');
        }
    }


    /**反馈历史**/
    public function refeedbkList(){
        $db_msg = M('daili_message');
        $where['uid'] = $_SESSION['uid'];
        $where_msg['uid'] = $_SESSION['uid'];
        $where_msg['status'] = array('in','0,1,2') ;
        $where['mid'] = 0 ;
        $res_msg = $db_msg -> where($where_msg) ->where($where) -> order('create_time DESC') -> select();
        foreach($res_msg as $k => $v){
            if($res_msg[$k]['status'] == 0){
                $res_msg[$k]['title'] = '等待客服处理中...';
            }elseif($res_msg[$k]['status'] == 1){
                $res_msg[$k]['title'] = '正在处理中...';
            }elseif($res_msg[$k]['status'] == 2){
                $res_msg[$k]['title'] = '回复成功，已关闭';
            }else{
                $res_msg[$k]['title'] = '系统错误，请联系客服';
            }
        }
        $this->assign('res_msg',$res_msg);
        $this->display();
    }


    /**反馈详情**/
    public function refeedbkin(){
        $db_msg = M('daili_message');
        $db_re = M('daili_message_reply');
        $where_msg['uid'] = $_SESSION['uid'];
        $where['id'] = I('id');
        $where_msg['status'] = array('in','1,2,4') ;
        $where['mid'] = 0 ;
        $res_msg = $db_msg -> where($where_msg) ->where($where) -> select();
        /**the following is the kernel code to show messages and replies(Home)**/
        foreach($res_msg as $k => $v) {
            $array[] = $res_msg[$k]['id'];
        }
        $where_msg['mid'] = array('in',$array);
        $where_re['msgid'] = array('in',$array);
        $where_re['status'] = array('in','1,2') ;
        $res_msg = $db_msg -> where($where_msg) -> order("create_time DESC") -> select();
        $res_re = $db_re -> where($where_re) -> order("create_time DESC") -> select();
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
        array_multisort(array_column($total,'create_time'),SORT_ASC,$total);   //将合并数组按照create_time升序排列
        $this->assign('total',$total);
        $this->display();
    }

}
