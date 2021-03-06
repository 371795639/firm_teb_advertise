<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/7/3
 * Time: 18:53
 */
namespace Admin\Controller;
use Think\Controller;
class GameController extends Controller{
    public $n;
    public function inTime(){
    error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :定时器启动\n",3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");    		
	$this->playList();
	$this->rechargeList();
	$this->makeRecharge();
    }
    //授权访问
    public function auth_curl(){
        header('Content-Type: text/html; charset=utf-8');
        $url = "http://119.23.60.80/admin/napp";
        $post_data = "api=auth&username=admin&passworld=admin123";
        $cookie_file = '/data/tuiguang/cookie/cookie.txt';
        //获取cookies并保存
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//返回字符串，而非直接输出
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);//存储cookies
        $output = curl_exec($ch);
        curl_close($ch);
    }

    /**获取玩家列表信息**/
    public function playList(){
    	error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :玩家列表定时器\n",3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
        header('Content-Type: text/html; charset=utf-8');
        
	/*获取上次更新的时间*/
	$file_path = "/data/tuiguang/logs/lasttime.txt";
	if(file_exists($file_path)){
 		$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
 		$str = str_replace("\r\n","<br />",$str);		
	}
	/*本次更新的开始时间和结束时间*/
	$start_time = $str + 1;
	$end_time = $str + 86400;
        /*接口授权*/
        $this->auth_curl();
        $url = "http://119.23.60.80/admin/napp";

        //参数
        $post_data = "api=userlist&tstart=".$start_time."&tend=".$end_time;
        $cookie_file = '/data/tuiguang/cookie/cookie.txt';
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
        $api_data = json_decode($response);
        $userList = std_class_object_to_array($api_data);
	error_log(print_r($userList['data'],1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
        if(!empty($userList['data'])){
            foreach ($userList['data'] as $key => $val){
                $name = urldecode($val["name"]);
                $userList['data'][$key]['name'] = urldecode($name);
                if(!array_key_exists('createBy',$val)){
                    $userList['data'][$key]['createBy'] = "";
                }
                $userList['data'][$key]['status'] = 0;
                $userList['data'][$key]['relation'] = "";
            }
            $this->n = $userList['data'];
            $this->gameRelation();
        }
    }
    public function gameRelation(){
        header('Content-Type:text/html;charset=utf-8');
        /**处理接口数据中的玩家关系**/
        $new_game = $this->n;
        $number = count($new_game);
        for ($i = 0;$i < $number;$i++){

            if($new_game[$i]['status'] == 0) {//如果该玩家关系没有修改的话
                if ($new_game[$i]['createBy'] == "") {
                    $new_game[$i]['status'] = 1;
                    $new_game[$i]['relation'] = "";
                } else {
                    $temp = $new_game[$i]['createBy'];//将该玩家的推荐人id赋值给$temp;
                    $s = 1;//循环条件；
                    while ($s == 1) {
                        for ($j = 0; $j < $number; $j++) {
                            if ($new_game[$j]['id'] == $temp) {//如果存在上级
                                if ($new_game[$j]['status'] == 1) {//判断上级是否已经修改，如已修改后则只需将上级的关系拼接即可
                                    if ($new_game[$j]['relation'] == "") {//判断关系值中是否有值，目的是为了去除最后的逗号；
                                        $new_game[$i]['relation'] = $new_game[$j]['id'];
                                    } else {
                                        $new_game[$i]['relation'] = $new_game[$j]['relation']."," .$new_game[$j]['id'];
                                    }
                                    $s = 0;
                                    break;
                                }
                                if ($new_game[$j]['status'] == 0) {//如上级的关系字段值没有被修改，则将上级绑定到自己的关系字段下；
                                    if ($new_game[$i]['relation'] == "") {//判断关系值中是否有值，目的是为了去除最后的逗号；
                                        $new_game[$i]['relation'] = $new_game[$j]['id'];
                                    } else {
                                        $new_game[$i]['relation'] = $new_game[$j]['id']. "," . $new_game[$i]['relation'];
                                    }
                                    if ($new_game[$j]['createBy'] == "") {//如上级为空了，关系已绑定完成了，修改状态；
                                        $s = 0;
                                        $new_game[$j]['status'] = 1;
                                        break;
                                    } else {
                                        $temp = $new_game[$j]['createBy'];//如果还有上级且上级关系字段值都没有修改的情况下继续寻找上级；
                                    }
                                }
                            }else{
                                $new_game[$i]['relation'] = $new_game[$i]['createBy'];
                            }
                            if ($j == ($number - 1)) {
                                if ($new_game[$j]['id'] != $temp) {
                                    $s = 0;
                                    break;
                                }
                            }
                        }
                    }
                    $new_game[$i]['status'] = 1;//循环结束后修改关系自段值修改的状态；
                }
            }
        }
        /**处理玩家的上级与上上级并写入数据库中**/
        for($m = 0;$m < $number;$m++){
            if($new_game[$m]['createBy'] == ""){
                $new_game[$m]['relation'] = $new_game[$m]['relation'];//完整的关系值（字符串）

            }else{
                //将关系字段值拼接完成
                $relation = M('user_ship')->field('superior,relation')->where(array('id'=>$new_game[$m]['createBy']))->find();
                $new_game[$m]['relation'] = $relation['relation'].$new_game[$m]['relation'];//完整的关系值（字符串）
                $new_relation = explode(',',$new_game[$m]['relation']);
                $new_res = array_reverse($new_relation);
                for ($n = 0;$n < count($new_res);$n++){
                    //判断上级是否存在推广专员
                    $recommend_id = M('staff')->where(array('game_id'=>$new_res[$n]))->getField('id');
                    //如果该玩家是推广专员
                    if($recommend_id){
                        $new_game[$m]['superior'] = $recommend_id;//该玩家的上级
                        break;
                    }
                }
            }
            //将该玩家信息存入数据库
            $data = array(
                'game_id' => $new_game[$m]['id'],
                'recommend' => $new_game[$m]['createBy'],
                'superior' => $new_game[$m]['superior'],
                'relation' => $new_game[$m]['relation'],
                'reg_time' => date('Y-m-d H:i:s',$new_game[$m]['regTime'])
            );
            M('user_ship')->add($data);
        }
    }
    

    /**获取充值列表**/
    public function rechargeList(){
    
        header('Content-Type: text/html; charset=utf-8');
        /*获取上次更新时间*/
	$file_path = "/data/tuiguang/logs/lasttime.txt";
	if(file_exists($file_path)){
 		$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
 		$str = str_replace("\r\n","<br />",$str);		
	}
	/*本次更新开始时间和结束时间*/
	$start_time = $str + 1;
	$end_time = $str + 86400;
	
	/*接口授权*/
        $this->auth_curl();
        
        $url = "http://119.23.60.80/admin/napp";
     
        $post_data = "api=rechargelist&tstart=".$start_time."&tend=".$end_time;
        $cookie_file = '/data/tuiguang/cookie/cookie.txt';
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
        $api_data = json_decode($response);
        $rechargeList = std_class_object_to_array($api_data);
	error_log(print_r($rechargeList['data'],1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
        if(!empty($rechargeList['data'])) {
            foreach ($rechargeList['data'] as $key => $val) {
                $name = urldecode($val["name"]);
                $rechargeList['data'][$key]['name'] = urldecode($name);
                if($val['orderFrom'] == 'webapp'){
                    $recharge_type = 1;
                }else{
                    $recharge_type = 2;
                }
                //获取该玩家所属的兑换中心id
                $superior_id = M('user_ship')->where(array('game_id'=>$val['id']))->getField('superior');
                if($superior_id != ""){
                    $service_number = M('staff')->where(array('id'=>$superior_id))->getField('service_number');
                }else{
                    $service_number = "";
                }
                //将获得数据写入数据库中
                $add_data = array(
                    'game_id'=>$val['id'],
                    'is_first'=>$val['isFirst'],
                    'money'=>$val['rmb'],
                    'type'=>$recharge_type,
                    'create_time' => date('Y-m-d H:i:s',$val['orderTime']),
                    'service_num' => $service_number
                );
		//error_log(print_r($add_data,1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
                M('user_charge')->add($add_data);
            }
        }
    }

    /**
     * 定时拨付充值业绩奖励
     */
    public function makeRecharge(){
    error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :结算充值奖励\n", 3, "/data/tuiguang/logs/".date("[Y-m-d]").".log");
        /*获取上次更新时间*/
	$file_path = "/data/tuiguang/logs/lasttime.txt";
	if(file_exists($file_path)){
 		$str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
 		$str = str_replace("\r\n","<br />",$str);		
	}
	/*本次更新开始时间和结束时间*/
	$start_time = $str + 1;
	$end_time = $str + 86400;
	//更新文件中更新时间
	file_put_contents($file_path, $end_time);
        $starts_time = date('Y-m-d H:i:s',$start_time);
        $ends_time = date('Y-m-d H:i:s',$end_time);
        //从充值列表中获取昨日充值的相应数据

        $map['create_time'] = array(array('egt',$starts_time), array('elt', $ends_time));
        $map['type'] = 1;
        $recharge_msg = M('user_charge')->where($map)->select();
	error_log("昨日所有充值记录：".print_r($recharge_msg,1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
        if(!empty($recharge_msg)){
            foreach ($recharge_msg as $key=>$val){
                $superior = M('user_ship')->where(array('game_id'=>$val['game_id']))->getField('superior');
                if(!empty($superior)){
                    $recharge_msg[$key]['superior'] = $superior;
                    $result[] = $superior;
                }
            }
            $res = array_unique ($result);
            foreach ($res as $key=>$value){
                $msg[$key]['recharge'] = 0;
                $msg[$key]['id'] = $value;
                $staff_msg = M('staff')->field('money,consume_coin,income,referee,service_number')->where(array('id'=>$value))->find();
                $msg[$key]['referee'] = $staff_msg['referee'];
                $msg[$key]['service_number'] = $staff_msg['service_number'];
		$msg[$key]['money'] = $staff_msg['money'];
		$msg[$key]['consume_coin'] = $staff_msg['consume_coin'];
		$msg[$key]['income'] = $staff_msg['income'];
                foreach ($recharge_msg as $item){
                    if($item['superior'] == $value){
                        $msg[$key]['recharge'] += $item['money'];
                    }
                }
            }
            error_log(date("[Y-m-d H:i:s]").print_r($msg,1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
            //循环发放奖励
            foreach ($msg as $values){
                if($values['recharge'] != 0){
	    	if(!empty($values['referee'])){
                    $last = M('staff')->field('money,consume_coin,income')->where(array('id'=>$values['referee']))->find();
                    $post_data['last']['id'] = $values['referee'];
                    $post_data['last']['money'] = $last['money'];
                    $post_data['last']['coin'] = $last['consume_coin'];
                    $post_data['last']['income'] = $last['income'];
                }else{
                    $post_data['last'] = array();
                }
                $post_data['my']['id'] = $values['id'];
                $post_data['my']['money'] = $values['money'];
                $post_data['my']['coin'] = $values['consume_coin'];
                $post_data['my']['income'] = $values['income'];
                $post_data['my']['service_number'] = $values['service_number'];
                error_log(date("[Y-m-d H:i:s]").print_r($post_data,1),3,"/data/tuiguang/logs/".date("[Y-m-d]").".log");
                recharge($post_data,$values['recharge']);
	    }
                
            }
        }
    }
}