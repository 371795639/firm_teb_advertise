<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// OneThink常量定义
const ONETHINK_VERSION    = '1.0.131218';
const ONETHINK_ADDON_PATH = './Addons/';

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null){
    $uid = is_null($uid) ? is_login() : $uid;
    return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str  要分割的字符串
 * @param  string $glue 分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ','){
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ','){
    return implode($glue, $arr);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
* 对查询结果集进行排序
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param array $sortby 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
    if(is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url){
    cookie('redirect_url', $url);
}

/**
 * 获取跳转页面URL
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url(){
    $url = cookie('redirect_url');
    return empty($url) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
    \Think\Hook::listen($hook,$params);
}

/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name){
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name){
    $class = get_addon_class($name);
    if(class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    }else {
        return array();
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()){
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Addons/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0){
    static $list;
    if(!($uid && is_numeric($uid))){ //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if(empty($list)){
        $list = S('sys_active_user_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if(isset($list[$key])){ //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $User = new User\Api\UserApi();
        $info = $User->info($uid);
        if($info && isset($info[1])){
            $name = $list[$key] = $info[1];
            /* 缓存用户 */
            $count = count($list);
            $max   = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_active_user_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0){
    static $list;
    if(!($uid && is_numeric($uid))){ //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if(empty($list)){
        $list = S('sys_user_nickname_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if(isset($list[$key])){ //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $info = M('Member')->field('nickname')->find($uid);
        if($info !== false && $info['nickname'] ){
            $nickname = $info['nickname'];
            $name = $list[$key] = $nickname;
            /* 缓存用户 */
            $count = count($list);
            $max   = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_user_nickname_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 获取分类信息并缓存分类
 * @param  integer $id    分类ID
 * @param  string  $field 要获取的字段名
 * @return string         分类信息
 */
function get_category($id, $field = null){
    static $list;

    /* 非法分类ID */
    if(empty($id) || !is_numeric($id)){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('sys_category_list');
    }

    /* 获取分类名称 */
    if(!isset($list[$id])){
        $cate = M('Category')->find($id);
        if(!$cate || 1 != $cate['status']){ //不存在分类，或分类被禁用
            return '';
        }
        $list[$id] = $cate;
        S('sys_category_list', $list); //更新缓存
    }
    return is_null($field) ? $list[$id] : $list[$id][$field];
}

/* 根据ID获取分类标识 */
function get_category_name($id){
    return get_category($id, 'name');
}

/* 根据ID获取分类名称 */
function get_category_title($id){
    return get_category($id, 'title');
}

/**
 * 获取文档模型信息
 * @param  integer $id    模型ID
 * @param  string  $field 模型字段
 * @return array
 */
function get_document_model($id = null, $field = null){
    static $list;

    /* 非法分类ID */
    if(!(is_numeric($id) || is_null($id))){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('DOCUMENT_MODEL_LIST');
    }

    /* 获取模型名称 */
    if(empty($list)){
        $map   = array('status' => 1, 'extend' => 1);
        $model = M('Model')->where($map)->field(true)->select();
        foreach ($model as $value) {
            $list[$value['id']] = $value;
        }
        S('DOCUMENT_MODEL_LIST', $list); //更新缓存
    }

    /* 根据条件返回数据 */
    if(is_null($id)){
        return $list;
    } elseif(is_null($field)){
        return $list[$id];
    } else {
        return $list[$id][$field];
    }
}

/**
 * 解析UBB数据
 * @param string $data UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data){
    //TODO: 待完善，目前返回原始数据
    return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null){

    //参数检查
    if(empty($action) || empty($model) || empty($record_id)){
        return '参数不能为空';
    }
    if(empty($user_id)){
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if($action_info['status'] != 1){
        return '该行为被禁用或删除';
    }

    //插入行为日志
    $data['action_id']      =   $action_info['id'];
    $data['user_id']        =   $user_id;
    $data['action_ip']      =   ip2long(get_client_ip());
    $data['model']          =   $model;
    $data['record_id']      =   $record_id;
    $data['create_time']    =   NOW_TIME;

    //解析日志规则,生成日志备注
    if(!empty($action_info['log'])){
        if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
            $log['user']    =   $user_id;
            $log['record']  =   $record_id;
            $log['model']   =   $model;
            $log['time']    =   NOW_TIME;
            $log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>NOW_TIME);
            foreach ($match[1] as $value){
                $param = explode('|', $value);
                if(isset($param[1])){
                    $replace[] = call_user_func($param[1],$log[$param[0]]);
                }else{
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
        }else{
            $data['remark'] =   $action_info['log'];
        }
    }else{
        //未定义日志规则，记录操作url
        $data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
    }

    M('ActionLog')->add($data);

    if(!empty($action_info['rule'])){
        //解析行为
        $rules = parse_action($action, $user_id);

        //执行行为
        $res = execute_action($rules, $action_info['id'], $user_id);
    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self){
    if(empty($action)){
        return false;
    }

    //参数支持id或者name
    if(is_numeric($action)){
        $map = array('id'=>$action);
    }else{
        $map = array('name'=>$action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();
    if(!$info || $info['status'] != 1){
        return false;
    }

    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = $info['rule'];
    $rules = str_replace('{$self}', $self, $rules);
    $rules = explode(';', $rules);
    $return = array();
    foreach ($rules as $key=>&$rule){
        $rule = explode('|', $rule);
        foreach ($rule as $k=>$fields){
            $field = empty($fields) ? array() : explode(':', $fields);
            if(!empty($field)){
                $return[$key][$field[0]] = $field[1];
            }
        }
        //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
        if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
            unset($return[$key]['cycle'],$return[$key]['max']);
        }
    }

    return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null){
    if(!$rules || empty($action_id) || empty($user_id)){
        return false;
    }

    $return = true;
    foreach ($rules as $rule){

        //检查执行周期
        $map = array('action_id'=>$action_id, 'user_id'=>$user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if($exec_count > $rule['max']){
            continue;
        }

        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        $field = $rule['field'];
        $res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

        if(!$res){
            $return = false;
        }
    }
    return $return;
}

//基于数组创建目录和文件
function create_dir_or_files($files){
    foreach ($files as $key => $value) {
        if(substr($value, -1) == '/'){
            mkdir($value);
        }else{
            @file_put_contents($value, '');
        }
    }
}

if(!function_exists('array_column')){
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 获取表名（不含表前缀）
 * @param string $model_id
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null){
    if(empty($model_id)){
        return false;
    }
    $Model = M('Model');
    $name = '';
    $info = $Model->getById($model_id);
    if($info['extend'] != 0){
        $name = $Model->getFieldById($info['extend'], 'name').'_';
    }
    $name .= $info['name'];
    return $name;
}

/**
 * 获取属性信息并缓存
 * @param  integer $id    属性ID
 * @param  string  $field 要获取的字段名
 * @return string         属性信息
 */
function get_model_attribute($model_id, $group = true){
    static $list;

    /* 非法ID */
    if(empty($model_id) || !is_numeric($model_id)){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('attribute_list');
    }

    /* 获取属性 */
    if(!isset($list[$model_id])){
        $map = array('model_id'=>$model_id);
        $extend = M('Model')->getFieldById($model_id,'extend');

        if($extend){
            $map = array('model_id'=> array("in", array($model_id, $extend)));
        }
        $info = M('Attribute')->where($map)->select();
        $list[$model_id] = $info;
        //S('attribute_list', $list); //更新缓存
    }

    $attr = array();
    foreach ($list[$model_id] as $value) {
        $attr[$value['id']] = $value;
    }

    if($group){
        $sort  = M('Model')->getFieldById($model_id,'field_sort');

        if(empty($sort)){	//未排序
            $group = array(1=>array_merge($attr));
        }else{
            $group = json_decode($sort, true);

            $keys  = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if(!empty($attr)){
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        $attr = $group;
    }
    return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}

/**
 * 根据条件字段获取指定表的数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @param string $table 需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null){
    if(empty($value) || empty($table)){
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M(ucfirst($table))->where($map);
    if(empty($field)){
        $info = $info->field(true)->find();
    }else{
        $info = $info->getField($field);
    }
    return $info;
}

/**
 * 获取链接信息
 * @param int $link_id
 * @param string $field
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url'){
    $link = '';
    if(empty($link_id)){
        return $link;
    }
    $link = M('Url')->getById($link_id);
    if(empty($field)){
        return $link;
    }else{
        return $link[$field];
    }
}

/**
 * 获取文档封面图片
 * @param int $cover_id
 * @param string $field
 * @return 完整的数据  或者  指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null){
    if(empty($cover_id)){
        return false;
    }
    $picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
    return empty($field) ? $picture : $picture[$field];
}

/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 * @param number $pos 推荐位的值
 * @param number $contain 指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0){
    if(empty($pos) || empty($contain)){
        return false;
    }

    //将两个参数进行按位与运算，不为0则表示$contain属于$pos
    $res = $pos & $contain;
    if($res !== 0){
        return true;
    }else{
        return false;
    }
}

/**
 * 获取数据的所有子孙数据的id值
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */

function get_stemma($pids,Model &$model, $field='id'){
    $collection = array();

    //非空判断
    if(empty($pids)){
        return $collection;
    }

    if( is_array($pids) ){
        $pids = trim(implode(',',$pids),',');
    }
    $result     = $model->field($field)->where(array('pid'=>array('IN',(string)$pids)))->select();
    $child_ids  = array_column ((array)$result,'id');

    while( !empty($child_ids) ){
        $collection = array_merge($collection,$result);
        $result     = $model->field($field)->where( array( 'pid'=>array( 'IN', $child_ids ) ) )->select();
        $child_ids  = array_column((array)$result,'id');
    }
    return $collection;
}


/**
 * print_r()函数优化
 */
function p($str){
    echo '<div style="border: 1px solid bisque;border-bottom-color:red;border-right-color:red;color:green;background-color: bisque "><pre>';
    print_r($str);
    echo '</pre></div>';
}


/**
 * var_dump()函数优化
 */
function v($str){
    echo '<div style="border: 1px solid bisque;color:green;background-color: bisque "><pre>';
    var_dump($str);
    echo '</pre></div>';
}


/**
 * 打印最近使用的SQL语句
 * @param $model
 */
function ps($model){
    $re = $model -> _sql();
    p($re);
}

/**
 * 生成订单号
 * @return string
 */
function make_orderId(){
    $mic     = explode(".",(microtime()));
    $mictime = $mic[1];
    $midtime = explode ( " ", $mictime);
    $reftime = $midtime[0];                                  //取微秒
    $time    = date("YmdHis",time());                        //取年月日时分                                                     //取年月日时分秒

    $sdcustomno  = $time.$reftime.rand(10,99);               //订单在商户系统中的流水号 商户信息+日期+随机数
    return $sdcustomno;
}

/**
 * 分享奖励
 * 说明：推荐第一个奖励100，第二个150，三个以后每个200
 * @param $recommend_num
 * @return int
 */
function recommend_awards($recommend_num){
    //首先判断该用户已经推荐了几人；第一个奖励100元，第二个150元，三个以后200元；
    if ($recommend_num == 1){
        $award = 100;
    }elseif ($recommend_num == 2){
        $award = 150;
    }else{
        $award = 200;
    }
    return $award;
}

/**
 * 中心充值业绩奖励结算
 * @param $type
 * @param $service_id
 * @param $uid
 * @param $money
 * @return bool
 */
function service_awards($type,$service_id,$money){
    //首先查询出各个参数比例
    $parameter = M('parameter')->field('value')->select();
    //首先获取所有的兑换中心的服务费比例
    $exchange = D('exchange');
    $exchange->serviceCharge($service_id);
    $result_message = $exchange->allMessages;

    //新推广专员注册获得的服务费
    if ($type == 'register'){
        $a = 0;
        foreach($result_message as $key=>$value){
            $b = $value['recommend_ratio'];
            $c = $b - $a;
            if($c > 0){
                $service_fee = $money * $c/100;//服务费
                $fact_service_fee = $service_fee * $parameter[3]['value'];
                $game_coin = $service_fee * $parameter[2]['value'];
                $order_id = make_orderId();//生成订单号
                $service_award[$key]['id'] = $value['apply_id'];
                $service_award[$key]['fact_money'] = $fact_service_fee;
                $service_award[$key]['coin'] = $game_coin;
                $service_award[$key]['money'] = $service_fee;
                $service_award[$key]['order_id'] = $order_id;
                $a = $b;//将服务费比例赋值给变量$a;
            }
        }
        return $service_award;
    }

    //昨日伞下人员充值服务费奖励
    if($type == 'recharge'){
        $start = 0;
        foreach($result_message as $key=>$value){
            $b = $value['recharge_ratio'];
            $c = $b - $start;
            if($c > 0){
                $service_fee = $money * $c/100;//服务费
                $fact_service_fee = $service_fee * $parameter[3]['value'];
                $game_coin = $service_fee * $parameter[2]['value'];
                $order_id = make_orderId();//生成订单号
                $service_award[$key]['id'] = $value['apply_id'];
                $service_award[$key]['fact_money'] = $fact_service_fee;
                $service_award[$key]['coin'] = $game_coin;
                $service_award[$key]['money'] = $service_fee;
                $service_award[$key]['order_id'] = $order_id;
                $start = $b;//将服务费比例赋值给变量$a;
            }
        }
        return $service_award;
    }
}

/**
 * [std_class_object_to_array 将对象转成数组]
 * @param [stdclass] $stdclassobject [对象]
 * @return [array] [数组]
 */
function std_class_object_to_array($stdclassobject){
    $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
    foreach ($_array as $key => $value) {
        $value = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
        $array[$key] = $value;
    }
    return $array;
}


/**
 * 支付各项奖励事务处理
 * @param $rewardMsg
 * @param $userMsg
 * @param $flowMsg
 * @param $noticeMsg
 */
function payReward($userMsg,$rewardMsg,$flowMsg,$noticeMsg){
    $staff = M('staff');
    $reward = M('reward');
    $flow = M('flow');
    $notice = M('notice');
    $staff->startTrans();//启用事务
    $result = true;
    //修改账户信息
    foreach ($userMsg as $val){
        $money_add  = $staff->where(array('id'=>$val['id']))->setInc('money',$val['data']['money']);
        $coin_add   = $staff->where(array('id'=>$val['id']))->setInc('consume_coin',$val['data']['consume_coin']);
        $income_add = $staff->where(array('id'=>$val['id']))->setInc('income',$val['data']['income']);
        if(!$money_add || !$coin_add || !$income_add){
            $result = false;
        }
    }
    //添加奖励发放记录
    foreach ($rewardMsg as $value){
        $reward_update = $reward->add($value['data']);
        if(!$reward_update){
            $result = false;
        }
    }
    //添加流水记录
    foreach ($flowMsg as $item){
        $flow_update = $flow->add($item['data']);
        if(!$flow_update){
            $result = false;
        }
    }
    //添加消息
    foreach ($noticeMsg as $vals){
        $notice_update = $notice->add($vals['data']);
        if(!$notice_update){
            $result = false;
        }
    }
    if($result == true){
        $staff->commit();//成功则提交
    }else{
        $staff->rollback();//不成功，则回滚
//        error_log(date("[Y-m-d H:i:s]")."false:",3,"1.log");
        error_log(date("[Y-m-d H:i:s]").print_r($result,1),3,"/data/tuiguang/logs/shiwu.log");
    }
}


/**
 * 提现事务
 * @param $cashMsg
 * @param $userMsg
 * @param $flowMsg
 * @param $noticeMsg
 * @return string
 */
function getCash($cashMsg,$userMsg,$flowMsg,$noticeMsg){
    $staff = M('staff');
    $withdraw = M('withdraw');
    $flow = M('flow');
    $notice = M('notice');
    $staff->startTrans();//启用事务
    //修改账户信息
    $staff_update = $staff->where(array('id'=>$cashMsg['id']))->setDec('money',$cashMsg['money']);
    //添加提现记录
    $withdraw_insert = $withdraw->add($userMsg);
    //添加流水记录
    $flow_update = $flow->add($flowMsg);
    //添加消息
    $notice_update = $notice->add($noticeMsg);
    if($staff_update && $withdraw_insert && $flow_update && $notice_update){
        $staff->commit();//成功则提交
        return "success";
    }else{
        $staff->rollback();//不成功，则回滚
        return "false";
    }
}

/**
 * 充值事务处理
 * @param $rechargeMsg
 * @param $userMsg
 * @param $flowMsg
 * @param $noticeMsg
 */
function makeRecharge($rechargeMsg,$userMsg,$flowMsg,$noticeMsg){
    $staff = M('staff');
    $recharge = M('recharge');
    $flow = M('flow');
    $notice = M('notice');
    $staff->startTrans();//启用事务
    //修改账户信息
    foreach ($userMsg as $val){
        $staff_update = $staff->where(array('id'=>$val['id']))->save($val['data']);
        if(!$staff_update){
            $error_staff[] = $val['id'];
        }
    }
    //添加充值记录
    foreach ($rechargeMsg as $value){
        $reward_update = $recharge->add($value['data']);
        if(!$reward_update){
            $error_reward[] = $value['id'];
        }
    }
    //添加流水记录
    foreach ($flowMsg as $item){
        $flow_update = $flow->add($item['data']);
        if(!$flow_update){
            $error_flow[] = $item['id'];
        }
    }
    //添加消息
    foreach ($noticeMsg as $vals){
        $notice_update = $notice->add($vals['data']);
        if(!$notice_update){
            $error_notice[] = $vals['id'];
        }
    }
    if(empty($error_staff) && empty($error_reward) && empty($error_flow) && empty($error_notice)){
        $staff->commit();//成功则提交
    }else{
        $staff->rollback();//不成功，则回滚
    }
}

/**
 * 中心推荐奖励拨付总额
 * @param $service_id
 * @param $money
 * @return mixed
 */
function service_recommend_award($service_id,$money){
    //首先获取所有的兑换中心的服务费比例
    $exchange = D('exchange');
    $exchange->serviceCharge($service_id);
    $result_message = $exchange->allMessages;
    //新推广专员注册获得的服务费
        $a = 0;
        $service_fee = 0;
        foreach($result_message as $key=>$value){
            $b = $value['recommend_ratio'];
            $c = $b - $a;
            if($c > 0){
                $service_fee += $money * $c/100;//服务费
                $a = $b;//将服务费比例赋值给变量$a;
            }
        }
        return $service_fee;
}

/**
 * 中心业绩奖励拨付总额
 * @param $service_id
 * @param $money
 * @return mixed
 */
function service_recharge_award($service_id,$money){
    //首先获取所有的兑换中心的服务费比例
    $exchange = D('exchange');
    $exchange->serviceCharge($service_id);
    $result_message = $exchange->allMessages;
    //新推广专员注册获得的服务费
    $a = 0;
    $service_fee = 0;
    foreach($result_message as $key=>$value){
        $b = $value['recharge_ratio'];
        $c = $b - $a;
        if($c > 0){
            $service_fee += $money * $c/100;//服务费
            $a = $b;//将服务费比例赋值给变量$a;
        }
    }
    return $service_fee;
}

/**
 * 个人分红拨付总额
 * @param $uid
 * @param $type
 * @param $money
 * @return float|int
 */
function bonus_personal($uid,$type,$money){
    $user = M('staff_info');
    //首先查询出每个人的固定分红点和信用分
    $user_msg = $user->field('fix_bonus,extra_bonus,credit_value')->where(array('uid'=>$uid))->find();
    if ($type == 1){//按照固定分红点发放游戏分红
        //运算出每个人应得的金额以及总金额
        $reward = $money * $user_msg['fix_bonus'] * $user_msg['credit_value']/100;//个人所得游戏分红金额
    }
    if ($type == 2){//按照所有的分红点发放游戏分红
        //运算出每个人应得的金额以及总金额
        $reward = $money * ($user_msg['fix_bonus'] + $user_msg['extra_bonus']) * $user_msg['credit_value']/100;
    }
    return $reward;
}

/**
 * 加盟商任务奖励
 * 任务奖励值=公司利润-直推奖励-业绩提成-中心业绩奖励-中心推荐奖励-分销奖励-分红奖励；
 * @param $rec_num 已经推荐推广专员人数
 * @param $share_id 推荐人id
 * @param $service_number 用户所属中心id
 * @param $uid 用户
 * @param $recharge 充值业绩指标
 * @return int
 */
function taskMoney($uid,$rec_num,$recharge,$service_number,$share_id){
        //首先查询出各个参数比例
        $parameter = M('parameter')->field('name,value')->select();
        //公司利润
        $company = ($rec_num * 1000 + $recharge) * 60/100;
        //直推奖励；首先查询已经推荐几个推广专员
        $recommend_num = M('staff')->where(array('id'=>$share_id,'is_league'=>0))->count();
        if($recommend_num == 0){
            if($rec_num == 1){
                $recommend_award = 100;
            }elseif($rec_num == 2){
                $recommend_award = 250;
            }else{
                $recommend_award = 250 + ($rec_num - 2) * 200;
            }
        }elseif ($recommend_num == 1){
            if($rec_num == 1){
                $recommend_award = 150;
            }else{
                $recommend_award = 150 + ($rec_num - 1) * 200;
            }
        }elseif($recommend_num >= 2){
            $recommend_award = $rec_num * 200;
        }
        //业绩提成
        $recharge_award = $recharge * $parameter[0]['value'];
        //分销奖励
        $distribution = $recharge_award * $parameter[1]['value'];
        if(!empty($service_number)){
            //中心业绩奖励
            $service_recharge = service_recharge_award($service_number,$recharge);
            //中心推荐奖励
            $re_money = $rec_num * 1000;
            $service_recommend = service_recharge_award($service_number,$re_money);
        }else{
            $service_recharge = 0;
            $service_recommend = 0;
        }
        //分红奖励
        $bonus = bonus_personal($uid,1,$parameter[4]['value']);
        $taskMoney = $company - $recommend_award - $recharge_award - $distribution - $service_recharge - $service_recommend - $bonus;
        if($taskMoney < 0){
            $taskMoney = 0;
        }
        return $taskMoney;
    }

/**
 * 推荐推广专员成功注册奖励拨付
 * @param $share_id
 */
function recommend($share_id){
    $share_num = M('staff')->where(array('referee'=>$share_id,'is_league'=>0))->count();
    //分享奖励发放
    $share_msg = M('staff')->field('money,consume_coin,income,service_number')->where(array('id'=>$share_id))->find();
    $share_award = recommend_awards($share_num);
    $order_id = make_orderId();//生成订单号
    $new_money = $share_msg['money'] + $share_award * 70/100;
    $new_coin = $share_msg['consume_coin'] + $share_award * 30/100;
    $new_income = $share_msg['income'] + $share_award;
    $user_msg[0]['id'] = $share_id;
    $user_msg[0]['data'] = array(
        'id'=>$share_id,
        'money'=>$new_money,
        'consume_coin'=>$new_coin,
        'income'=>$new_income,
    );
    //分享奖励发放流水
    $flow_msg[0]['id'] = $share_id;
    $flow_msg[0]['data'] = array(
        'uid'=>$share_id,
        'type'=>2,
        'money'=>$share_award,
        'order_id'=>$order_id,
    );
    //分享奖励记录
    $reward_msg[0]['id'] = $share_id;
    $reward_msg[0]['data'] = array(
        'uid'=>$share_id,
        'type'=>3,
        'money'=>$share_award*70/100,
        'game_coin'=>$share_award*30/100,
        'order_id'=>$order_id,
        'remarks'=>"分享奖励，奖励金额￥".$share_award."元"
    );
    //金额变动消息
    $notice_msg[0]['id'] = $share_id;
    $notice_msg[0]['data'] = array(
        'uid'=>$share_id,
        'poster'=>'system',
        'kind'=>2,
        'notice_title'=>"分享奖励",
        'notice_content'=>"恭喜您推荐成功注册一名推广专员，奖励￥".$share_award."元。",
        'notice_type_id'=>3
    );
    /*中心分享奖励发放*/
    if(!empty($share_award['service_number'])){
        $service_award = service_awards('register',$share_award['service_number'],1000);
        foreach ($service_award as $key=>$val){
            $concert_msg = M('staff')->where(array('id'=>$val['id']))->find();
            //拨付
            $money = $concert_msg['money'] + $val['fact_money'];
            $coin = $concert_msg['consume_coin'] + $val['coin'];
            $income = $concert_msg['income'] + $val['money'];
            $new_key = $key + 1;
            $user_msg[$new_key]['id'] = $val['id'];
            $user_msg[$new_key]['data'] = array(
                'id'=>$val['id'],
                'money'=>$money,
                'consume_coin'=>$coin,
                'income'=>$income,
            );
            //中心分享奖励发放流水
            $flow_msg[$new_key]['id'] = $val['id'];
            $flow_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'type'=>6,
                'money'=>$money,
                'order_id'=>$val['order_id'],
            );
            //中心分享奖励记录
            $reward_msg[$new_key]['id'] = $val['id'];
            $reward_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'type'=>5,
                'money'=>$val['fact_money'],
                'game_coin'=>$val['coin'],
                'order_id'=>$val['order_id'],
                'remarks'=>"兑换中心分享奖励，奖励金额￥".$val['money']."元"
            );
            //金额变动消息
            $notice_msg[$new_key]['id'] = $val['id'];
            $notice_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'poster'=>'system',
                'kind'=>2,
                'notice_title'=>"兑换中心分享奖励",
                'notice_content'=>"恭喜您的团队推荐成功注册一名推广专员，奖励￥".$val['money']."元。",
                'notice_type_id'=>3
            );
        }
    }
    payReward($user_msg,$reward_msg,$flow_msg,$notice_msg);
}

/**
 * 玩家充值，用户奖励结算
 * @param $msg
 * @param $recharge
 */
function recharge($msg,$recharge){//todo查看gameController的备注
    //充值提成
    $recharge_draw = $recharge * 20/100;
    $order_id1 = make_orderId();//生成订单号
    $user_msg[0]['id'] = $msg['my']['id'];
    $user_msg[0]['data'] = array(
        'id'=>$msg['my']['id'],
        'money'=>$msg['my']['money'] + $recharge_draw * 70/100,
        'consume_coin'=>$msg['my']['coin'] + $recharge_draw * 30/100,
        'income'=>$msg['my']['money'] + $recharge_draw,
    );
    //充值提成发放流水
    $user_msg[0]['id'] = $msg['my']['id'];
    $user_msg[0]['data'] = array(
        'uid'=>$msg['my']['id'],
        'type'=>4,
        'money'=>$recharge_draw,
        'order_id'=>$order_id1,
    );
    //充值提成记录
    $reward_msg[0]['id'] = $msg['my']['id'];
    $reward_msg[0]['data'] = array(
        'uid'=>$msg['my']['id'],
        'type'=>4,
        'money'=>$recharge_draw * 70/100,
        'game_coin'=>$recharge_draw * 30/100,
        'order_id'=>$order_id1,
        'remarks'=>"充值提成奖励，奖励金额￥".$recharge_draw."元"
    );
    //金额变动消息
    $notice_msg[0]['id'] = $msg['my']['id'];
    $notice_msg[0]['data'] = array(
        'uid'=>$msg['my']['id'],
        'poster'=>'system',
        'kind'=>2,
        'notice_title'=>"充值提成奖励",
        'notice_content'=>"恭喜您的辖下有玩家充值，奖励￥".$recharge_draw."元。",
        'notice_type_id'=>3
    );
    //分销奖励
    /*判断是否有上级推广专员*/
    if(!empty($msg['last'])){
        $distribution = $recharge * 20 * 50/10000;
        $order_id2 = make_orderId();//生成订单号
        $user_msg[1]['id'] = $msg['last']['id'];
        $user_msg[1]['data'] = array(
            'id'=>$msg['last']['id'],
            'money'=>$msg['last']['money'] + $distribution * 70/100,
            'consume_coin'=>$msg['last']['coin'] + $distribution * 30/100,
            'income'=>$msg['last']['money'] + $distribution,
        );
        //分销发放流水
        $user_msg[1]['id'] = $msg['last']['id'];
        $user_msg[1]['data'] = array(
            'uid'=>$msg['last']['id'],
            'type'=>8,
            'money'=>$distribution,
            'order_id'=>$order_id2,
        );
        //分销奖励记录
        $reward_msg[1]['id'] = $msg['last']['id'];
        $reward_msg[1]['data'] = array(
            'uid'=>$msg['last']['id'],
            'type'=>7,
            'money'=>$distribution * 70/100,
            'game_coin'=>$distribution * 30/100,
            'order_id'=>$order_id2,
            'remarks'=>"分销奖励，奖励金额￥".$distribution."元"
        );
        //金额变动消息
        $notice_msg[1]['id'] = $msg['last']['id'];
        $notice_msg[1]['data'] = array(
            'uid'=>$msg['last']['id'],
            'poster'=>'system',
            'kind'=>2,
            'notice_title'=>"分销奖励",
            'notice_content'=>"恭喜您的下级推广专员获得充值提成，奖励￥".$distribution."元。",
            'notice_type_id'=>3
        );
    }
    //兑换中心业绩奖励
    /*判断是否有兑换中心*/
    if(!empty($msg['my']['service_number'])){
        $service_recharge = service_awards('recharge',$msg['my']['service_number'],$recharge);
        foreach ($service_recharge as $key=>$val){
            $concert_msg = M('staff')->where(array('id'=>$val['id']))->find();
            //拨付
            $money = $concert_msg['money'] + $val['fact_money'];
            $coin = $concert_msg['consume_coin'] + $val['coin'];
            $income = $concert_msg['income'] + $val['money'];
            $new_key = $key + 2;
            $user_msg[$new_key]['id'] = $val['id'];
            $user_msg[$new_key]['data'] = array(
                'id'=>$val['id'],
                'money'=>$money,
                'consume_coin'=>$coin,
                'income'=>$income,
            );
            //中心业绩奖励发放流水
            $flow_msg[$new_key]['id'] = $val['id'];
            $flow_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'type'=>6,
                'money'=>$money,
                'order_id'=>$val['order_id'],
            );
            //中心业绩奖励记录
            $reward_msg[$new_key]['id'] = $val['id'];
            $reward_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'type'=>6,
                'money'=>$val['fact_money'],
                'game_coin'=>$val['coin'],
                'order_id'=>$val['order_id'],
                'remarks'=>"兑换中心业绩奖励，奖励金额￥".$val['money']."元"
            );
            //金额变动消息
            $notice_msg[$new_key]['id'] = $val['id'];
            $notice_msg[$new_key]['data'] = array(
                'uid'=>$val['id'],
                'poster'=>'system',
                'kind'=>2,
                'notice_title'=>"兑换中心业绩奖励",
                'notice_content'=>"恭喜您的团队辖下有玩家充值，奖励￥".$val['money']."元。",
                'notice_type_id'=>3
            );
        }
    }
    makeRecharge($user_msg,$reward_msg,$flow_msg,$notice_msg);
}