<?php

namespace Home\Controller;

class WeeklyAutoController{

    /**
     * 自动发布任务
     * 任务表中存在本周任务但未发布，自动发布；
     * 任务表中不存在本周任务时，复制上次任务发布数据，并自动发布；
     */
    public function autoWeekTask(){
        $dbtask         = D('Task');
        $dbtaskWeekly   = D('TaskWeekly');
        $date           = date('Y-m-d H:i:s');
        $start          = $dbtaskWeekly -> get_start_time($date);
        $end            = $dbtaskWeekly -> get_end_time($date);
        $lastMon        = get_last_monday($date);
        $lastSun        = get_last_sunday($date);
        $lastLastMon    = get_last_last_monday($date);
        $lastLastSun    = get_last_last_Sunday($date);
        $mapWeekly      = array(
            'start_time'    => $start,
            'end_time'      => $end,
            'status'        => '1',
        );
        $resWeekly      = $dbtaskWeekly -> get_weekly_by_map($mapWeekly);
        if(empty($resWeekly) || count($resWeekly) <= 15){
            //task表中查找create_time在上周的任务
            $mapTask = array(
                'create_time'   => array(array('egt',$lastMon),array('lt',$lastSun)),
                'stutus'        => 1,
            );
            $resTask    = $dbtask -> get_task_by_map($mapTask);
            if(empty($resTask)){
                //获取上上周任务
                $mapLastLastTask = array(
                    'create_time'   => array(array('gt',$lastLastMon),array('lt',$lastLastSun)),
                    'stutus'        => 1,
                );
                $resLastLastTask = $dbtask -> get_task_by_map($mapLastLastTask);
                if(empty($resLastLastTask)){
                    //上上周任务为空说明是之前没有发布过任务或者任务发布出现断层，需手动发布任务。
                    $explain = '初始化或者任务出现发布出现断层，不执行代码。';
                }else {
                    foreach ($resLastLastTask as $k => $v) {
                        $dataLastLast['name']       = $resLastLastTask[$k]['name'];
                        $dataLastLast['type']       = $resLastLastTask[$k]['type'];
                        $dataLastLast['detail']     = $resLastLastTask[$k]['detail'];
                        $dataLastLast['class']      = $resLastLastTask[$k]['class'];
                        $dataLastLast['isgame']     = $resLastLastTask[$k]['isgame'];
                        $dataLastLast['inneed']     = $resLastLastTask[$k]['inneed'];
                        $dataLastLast['create_time']= $lastMon;
                        $dataLastLast['tasker']     = 'auto';
                        $dataLastLast['status']     = 1;
                        //把上上周的任务复制一份插入到表中
                        $dbtask -> add($dataLastLast);
                    }
                    //同时插入task_weekly表中并发布任务
                    $mapLastTask = array(
                        'create_time'   => array(array('egt', $lastMon), array('lt', $lastSun)),
                        'stutus'        => 1,
                    );
                    $resLastTask = $dbtask->get_task_by_map($mapLastTask);
                    foreach ($resLastTask as $k => $v) {
                        $dataLastWeek['task_id']    = $resLastTask[$k]['id'];
                        $dataLastWeek['name']       = $resLastTask[$k]['name'];
                        $dataLastWeek['post_time']  = $resLastTask[$k]['create_time'];
                        $dataLastWeek['start_time'] = $start;
                        $dataLastWeek['end_time']   = $end;
                        $dataLastWeek['tasker']     = 'auto';
                        $dataLastWeek['status']     = '1';
                        $dbtaskWeekly->add($dataLastWeek);
                    }
                    $explain = '把上上周的任务复制一份插入task表中，同时插入task_weekly表中并发布任务。';
                }
            }else{  //将已创建但未发布的任务发布。
                foreach($resTask as $k => $v){
                    $data['task_id']    = $resTask[$k]['id'];
                    $data['name']       = $resTask[$k]['name'];
                    $data['post_time']  = $resTask[$k]['create_time'];
                    $data['start_time'] = $start;
                    $data['end_time']   = $end;
                    $data['tasker']     = 'auto';
                    $data['status']     = '1';
                    $dbtaskWeekly -> add($data);
                }
                $explain = '将已创建但未发布的任务发布。';
            }
        }else{
            //周任务列表中任务已存在，不用进行任何操作。
            $explain = '本周任务已发布，不执行代码。';
        }
        error_log(date("[Y-m-d H:i:s]").':'.print_r($explain,1),3,"/data/tuiguang/logs/taskAuto.log");
    }
}
