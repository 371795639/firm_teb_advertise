<?php

namespace Home\Controller;
use Think\Controller;

class FeedController extends HomeController {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this -> redirect('Index/index');
	}


    /**
     * Message（反馈表）中status字段说明
     *状态为0：等待客服处理
     *状态为1：客服正在处理中，客户回复信息将记录在第一条反馈信息之下
     *状态为2：客服处理完成，客户反馈，将成为新的反馈信息
     *状态为4：客户针对当前反馈的回复信息
     * 不喜欢3，所以状态只有0,1,2,4
     */

    /**
     * MessageReply（回复表）中status字段说明
     *状态为0：未处理
     *状态为1：正在处理中
     *状态为2：处理完成
     */
    public function index(){
        
        $this -> display();
    }

    /**意见反馈**/
    public function refeedbk(){
        $dbMsg = M('Message');
        $data['uid'] = $_SESSION['uid'];
        $data['content'] = I('content');
        $data['create_time'] = time();
        if(!empty($data['content'])){
            $dbMsg -> data($data) -> add();
        }
        $this -> assign('data',$data);
        $this -> display();
    }


    /**加载继续反馈页面**/
    public function feedbker(){
        $data['mid'] = I('id');
        $data['aid'] = I('aid');
        $this -> assign('data',$data);
        $this -> display();
    }


    /**继续反馈动作**/
    public function refeedbkerr(){
        $dbMsg = M('Message');
        $data['content'] = I('content');
        $data['create_time'] = time();
        $data['status'] = 4;
        $data['uid'] = $_SESSION['uid'];
        $data['mid'] = I('mid');
        $data['aid'] = I('aid');
        if(!empty($data['content'])){
            $dbMsg -> data($data) -> add();
            $this -> redirect('home/feed/refeedbkList','回复成功，跳转中...');
        }
    }


    /**反馈历史**/
    public function refeedbkList(){
        $dbMsg = M('Message');
        $where['uid'] = $_SESSION['uid'];
        $whereMsg['uid'] = $_SESSION['uid'];
        $whereMsg['status'] = array('in','0,1,2') ;
        $where['mid'] = 0 ;
        $resMsg = $dbMsg  ->  where($whereMsg)  -> where($where)  ->  order('create_time DESC')  ->  select();
        foreach($resMsg as $k => $v){
            if($resMsg[$k]['status'] == 0){
                $resMsg[$k]['title'] = '等待客服处理中...';
            }elseif($resMsg[$k]['status'] == 1){
                $resMsg[$k]['title'] = '正在处理中...';
            }elseif($resMsg[$k]['status'] == 2){
                $resMsg[$k]['title'] = '回复成功，已关闭';
            }else{
                $resMsg[$k]['title'] = '系统错误，请联系客服';
            }
        }
        $this -> assign('resMsg',$resMsg);
        $this -> display();
    }


    /**反馈详情**/
    public function refeedbkin(){
        $dbMsg = M('Message');
        $dbRe = M('MessageReply');
        $whereMsg['uid'] = $_SESSION['uid'];
        $where['id'] = I('id');
        $whereMsg['status'] = array('in','1,2,4') ;
        $where['mid'] = 0 ;
        $resMsg = $dbMsg  ->  where($whereMsg)  -> where($where)  ->  select();
        /**the following is the kernel code to show messages and replies(Home)**/
        foreach($resMsg as $k => $v) {
            $array[] = $resMsg[$k]['id'];
        }
        $whereMsg['mid'] = array('in',$array);
        $whereRe['msgid'] = array('in',$array);
        $whereRe['status'] = array('in','1,2') ;
        $resMsg = $dbMsg  ->  where($whereMsg)  ->  order("create_time DESC")  ->  select();
        $resRe = $dbRe  ->  where($whereRe)  ->  order("create_time DESC")  ->  select();
        foreach ($resMsg as $k => $v) {
            $resMsg[$k]['msgid'] = 0;
            $resMsg[$k]['re_content'] = null;
            $resMsg[$k]['cid'] = I('id');
        }
        foreach ($resRe as $k => $v) {
            $resRe[$k]['mid'] = 0;
            $resRe[$k]['content'] = null;
            $resRe[$k]['cid'] = I('id');
        }
        $total = array_merge($resMsg,$resRe);
        array_multisort(array_column($total,'create_time'),SORT_ASC,$total);   //将合并数组按照create_time升序排列
        $this -> assign('total',$total);
        $this -> display();
    }

}
