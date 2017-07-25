<?php

namespace Home\Model;
use Think\Model;

class ExchangeModel extends Model {
    public $allMessages;

    /**
     * 新版加盟商系统中所有的上级兑换中心
     * @param $service_number
     */
    public function serviceCharge($service_number){
        $map['status'] = 2;
        $map['id'] = $service_number;
        $result = $this->field('id,apply_id,exchange_class,superior_number,recommend_ratio,recharge_ratio')->where($map)->find();
        $this->allMessages[] = $result;
        if($result['superior_number'] != 1 && $result['superior_number'] != null){
            $this->serviceCharge($result['superior_number']);
        }
    }
}
