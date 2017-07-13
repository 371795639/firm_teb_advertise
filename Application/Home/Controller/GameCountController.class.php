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
		$cookie_file = dirname(__FILE__).'/cookie.txt';
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
		$uidGroup       = $dbTaskDone -> get_this_week_all_task('','uid');
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
				return $gameResult;
			}
		}
	}
}
