<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

class ParameterModel extends Model{

    /**
     * 根据ID获取值
     * @param $id
     * @return bool|mixed
     */
    public function get_parameter_by_id($id){
        $re = $this -> where(array('id' => $id)) -> find();
        if($re){
            return $re;
        }else{
            return false;
        }
    }
}
