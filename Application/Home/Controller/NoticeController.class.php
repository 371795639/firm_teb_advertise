<?php

namespace Home\Controller;
use Think\Controller;

class NoticeController extends HomeController {

    /**消息首页**/
    public function index(){

        $this -> display();
    }

//任务模块是否完成游戏任务和充值业绩等待商议结果，现在开始做消息模块

    /**默认消息展示页面**/
    public function noticeDetail(){

        $this -> display();
    }


    /**系统公告**/
    public function sysNotice(){
        $dbNotice = D('Notice');
        $sysNotice = $dbNotice -> get_notice_by_type('1');
        $date = date('Y-m-d');
        foreach($sysNotice as $k => $v){
            if(time_formatiss($sysNotice[$k]['create_time']) == $date){
                $sysNotice[$k]['create_time'] = time_formatsss($sysNotice[$k]['create_time']);
            }else{
                $sysNotice[$k]['create_time'] = time_formatiss($sysNotice[$k]['create_time']);
            }
        }
        $this -> assign('sysNotice',$sysNotice);
        $this -> display();
    }

// 消息页面 需不需要图片上传？ 公告主题和公告内容 需要哪一个？
// 任务消息和任务的关系？
// 活动提醒消息是什么？
// 资金变动怎么？

// 目的：判断是否完成三次游戏任务
// 需求：接口的返回值直接给我游戏任务的状态值就行了 => 同时完成三个任务，返回1；三个任务中只要有一个未完成的，返回0





}
