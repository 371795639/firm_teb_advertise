<?php

namespace Home\Controller;

class GameCountController{

	/**
	 * 调用接口获取玩家信息
	 * @param $uid      integer 玩家ID
	 * @return mixed
	 */
	public function get_game_api($uid){
		$api = A('index');
		$api -> getApi();
		$url = "http://119.23.60.80/admin/napp";
		$post_data = "api=playlist&userlist=".$uid;
		$cookie_file = '/data/tuiguang/cookie/cookie.txt';      //线上
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// post数据
		curl_setopt($ch, CURLOPT_POST, 1);
		// post的变量
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$response = curl_exec($ch);
		curl_close($ch);
		$apiData = json_decode($response);
		$apiGameTask = std_class_object_to_array($apiData);
		return $apiGameTask;
	}


	/**日定时器获取playCount**/
	public function gameGet(){
		//首先获取done表中本周内已领取任务的所有用户uid，根据uid找出game_id，然后根据game_id调用接口返回playCount
		$dbStaff        = D('Staff');
		$dbTaskDone     = D('TaskDone');
		$dbGameCount    = D('GameCount');
        $date           = date('Y-m-d H:i:s');
        $timeBegin      = $dbTaskDone -> get_monday_time($date,'00:00:00');                 //周一零点
        $timeEnd        = $dbTaskDone -> get_monday_time($date,'23:59:59');                 //周一晚12点
        //当前时间是周一，则获取上周的数据，反之获取本周的数据
        if($timeBegin < $date && $date < $timeEnd){
            $uidGroup   = $dbTaskDone -> get_last_week_done_group($date,'','uid','select'); //上周内领取日常任务的任务列表
        }else{
            $uidGroup   = $dbTaskDone -> get_this_week_all_task('','uid');			        //本周内领取日常任务的任务列表
        }
		if(!empty($uidGroup)) {
			foreach ($uidGroup as $k => $v) {
				$uid        = $uidGroup[$k]['uid'];
				$staffs     = $dbStaff -> get_staff_by_id($uid);
				$gamesId    = $staffs['game_id'];
				$gamesIds[] = $staffs['game_id'];
				$GameCountRe= $dbGameCount-> get_game($uid,'find');
				if (empty($GameCountRe)) {
					$dateGame   = array(
						'uid'   => $uid,
						'gameId'=> $gamesId,
						'time'  => date('Y-m-d H:i:s'),
					);
					$dbGameCount->add($dateGame);
				}
			}
			$gamesIds   = implode(',', $gamesIds);
            error_log(date("[Y-m-d H:i:s]").'调用接口时传过去的所有用户的游戏ID:'.print_r($gamesIds,1),3,"/data/tuiguang/logs/gameCount.log");
			$gameResult = $this -> get_game_api($gamesIds);
			if($gameResult['error'] == 0){
				$gamesResult= $gameResult['data'];
				foreach ($gamesResult as $k => $v) {
					$uid    = $gamesResult[$k]['uid'];
					$count  = $gamesResult[$k]['playCount'];
					$datas['playCount'] = $count;
					$dbGameCount -> save_game('gameId',$uid,$datas);
				}
			}else{
                error_log(date("[Y-m-d H:i:s]").'接口调用失败，反馈数据信息为:'.print_r($gameResult,1),3,"/data/tuiguang/logs/gameCount.log");
			}
		}else{
            error_log(date("[Y-m-d H:i:s]").'未获取到上周数据'.print_r($uidGroup,1),3,"/data/tuiguang/logs/gameCount.log");
        }
	}
}
