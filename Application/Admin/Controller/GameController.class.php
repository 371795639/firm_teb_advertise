<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2017/7/3
 * Time: 18:53
 */
namespace Admin\Controller;
use Think\Controller;
class GameController extends AdminController{
    public function gameRelation(){
        header('Content-Type:text/html;charset=utf-8');
        //首先接受接口的数据
        $new_game = array(
            array(
                'game_id'=>1010,
                'referee_id'=>1009,
                'reg_time'=>'2017-06-05 21:52:00',
                'relation'=>'',
                'status'=> 0
            ),
            array(
                'game_id'=>1011,
                'referee_id'=>1010,
                'reg_time'=>'2017-06-05 21:52:00',
                'relation'=>'',
                'status'=> 0
            ),
            array(
                'game_id'=>1012,
                'referee_id'=>'',
                'reg_time'=>'2017-06-05 21:52:00',
                'relation'=>'',
                'status'=> 0
            ),
        );
        //处理接口数据
        /**处理接口数据中的玩家关系**/
        $number = count($new_game);
        for ($i = 0;$i < $number;$i++){
            if($new_game[$i]['status'] == 0){//如果该玩家关系没有修改的话
                $temp = $new_game[$i]['referee_id'];//将该玩家的推荐人id赋值给$temp;
                $s = 1;//循环条件；
                while ($s == 1){
                    for ($j = 0;$j < $number;$j++){
                        if ($new_game[$j]['game_id'] == $temp){//如果存在上级
                            if($new_game[$j]['status'] == 1){//判断上级是否已经修改，如已修改后则只需将上级的关系拼接即可
                                if($new_game[$j]['relation'] == ""){//判断关系值中是否有值，目的是为了去除最后的逗号；
                                    $new_game[$i]['relation'] = $new_game[$j]['game_id'];
                                }else{
                                    $new_game[$i]['relation'] = $new_game[$j]['game_id'].",".$new_game[$j]['relation'];
                                }
                                $s = 0;
                                break;
                            }
                            if($new_game[$j]['status'] == 0){//如上级的关系字段值没有被修改，则将上级绑定到自己的关系字段下；
                                if($new_game[$i]['relation'] == ""){//判断关系值中是否有值，目的是为了去除最后的逗号；
                                    $new_game[$i]['relation'] = $new_game[$j]['game_id'];
                                }else{
                                    $new_game[$i]['relation'] = $new_game[$j]['game_id']. "," . $new_game[$i]['relation'];
                                }
                                if($new_game[$j]['referee_id'] == ""){//如上级为空了，关系已绑定完成了，修改状态；
                                    $s = 0;
                                    $new_game[$j]['status'] = 1;
                                    break;
                                }else {
                                    $temp = $new_game[$j]['referee_id'];//如果还有上级且上级关系字段值都没有修改的情况下继续寻找上级；
                                }
                            }
                        }
                        if($j == ($number - 1)){
                            if($new_game[$j]['game_id'] != $temp){
                                $s = 0;
                                break;
                            }
                        }
                    }
                }
                $new_game[$i]['status'] = 1;//循环结束后修改关系自段值修改的状态；
            }
        }

        /**处理玩家的上级与上上级并写入数据库中**/
        for($m = 0;$m < $number;$m++){
            //将关系字段值拼接完成
            $relation = M('user_ship')->field('superior,relation')->where(array('game_id'=>$new_game[$m]['referee_id']))->find();
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
            //将该玩家信息存入数据库
            $data = array(
                'game_id' => $new_game[$m]['game_id'],
                'superior' => $new_game[$m]['superior'],
                'relation' => $new_game[$m]['relation'],
                'reg_time' => $new_game[$m]['reg_time']
            );
            M('user_ship')->add($data);
        }
    }
    //url:$_SEVERE['SEVER_HOST']/index.php?s=/admin/game/gameRelation

//    public function gameRecharge(){
//        header('Content-Type:text/html;charset=utf-8');
//        $curl = curl_init();
//        //设置提交的url
//        curl_setopt($curl, CURLOPT_URL, "http://119.23.60.80/admin/napp");
//        //设置头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        //设置获取的信息以文件流的形式返回，而不是直接输出。
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        //设置post方式提交
//        curl_setopt($curl, CURLOPT_POST, 1);
//        //设置post数据
//        $post_data = array('api'=>'auth','username'=>'admin','passworld'=>'123456');
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
//        //执行命令
//        $data = curl_exec($curl);
//        //关闭URL请求
//        curl_close($curl);
//        //获得数据并返回
//        return $data;
//        //充值类型 1：微信充值；2：支付宝；3：网银；4：系统赠送；5：其他
//    }
}