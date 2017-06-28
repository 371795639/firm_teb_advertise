<?php

namespace Home\Controller;
use Think\Controller;

class FeedController extends HomeController {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this -> redirect('Index/index');
	}


    /**反馈页面**/
    public function index(){
        $dbMsg = D('Message');
        $msgRe = D('MessageReply');
        $where = array(
            'uid'       => $_SESSION['userid'],
            'status'    => array('in','1,2'),
        );
        $resMsg = $dbMsg -> where($where) -> order('id desc')-> find();
        $id = $resMsg['id'];
        $resMsgRe = $msgRe -> get_reply_by_msgid($id);
        if($resMsg){
            $show = 1;  //显示用户反馈与回复历史
        }else{
            $show = 2;  //展示
        }
        //获取上一条已完成的回复
        $condition = array(
            'uid'       => $_SESSION['userid'],
            'status'    => 2,
        );
        $resMsgNew = $dbMsg -> where($condition) -> order('id desc')-> find();
        $idNew = $resMsgNew['id'];
        $resMsgReNew = $msgRe -> get_reply_by_msgid($idNew);
        $this -> assign('resMsgRe',$resMsgRe);
        $this -> assign('resMsgNew',$resMsgNew);
        $this -> assign('resMsgReNew',$resMsgReNew);
        $this -> assign('show',$show);
        $this -> assign('resMsg',$resMsg);
        $this -> display();
    }

    /**意见反馈**/
    public function feed(){
        $dbMsg = M('Message');
        $content = I('content');
        if(empty($content)){
            $this -> error('意见反馈没内容，你逗我玩呢？');
        }else{
            $data = array(
                'uid'           => $_SESSION['userid'],
                'content'       => $content,
                'create_time'   => date('Y-m-d H:i:s'),
                'reply_time'    => '',
                'status'        => 1,
            );
            $resMsg = $dbMsg -> data($data) -> add();
        }
        if($resMsg){
            $this -> success('反馈成功',U('User/index'));
        }else{
            $this -> error('反馈失败....');
        }

    }












    //功能简化 整篇重做



















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
        $data['uid'] = $_SESSION['userid'];
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
        $where['uid'] = $_SESSION['userid'];
        $whereMsg['uid'] = $_SESSION['userid'];
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
        $whereMsg['uid'] = $_SESSION['userid'];
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
