<?php

/**
 * @description of redis_server | (Hzj)
 * @version 1.5.0
 * @version 1.6.20230815 [Hzj.增加数据库分区号配置(Fun:redis_setDb)]
 ***/

class redis_server
{
    var $redisCount;
    public function __construct($hostInfo,$time=10)
    {#Redis超时时间10s
        $host       = $hostInfo["host"];
        $port       = $hostInfo["port"];
        $passWord   = $hostInfo["password"];
        try {
            $redis = new \Redis();
            $redis->connect($host, $port, $time);//连接参数：ip、端口、连接超时时间，连接成功返回true，否则返回false
            if($passWord != null){
                $redis->auth($passWord);//密码认证：成功返回true，否则返回false
            }
            $this->redisCount = $redis;
        } catch (Exception $exc) {
            repose::return_Data('3001', ' Redis Connect Failed! ');
        }
    }
    
    /**
     * @TODO设置REID的存储分区
     * @param Integer $selectKey
     */
    public function redis_setDb($selectKey=0)
    {
        $redisServer = $this->redisCount;
        $setDb = $redisServer->select($selectKey);
        if($setDb == false){ //分区不存在
           $redisServer->select(0); //默认存储在0分区
        }
        $this->redisCount = $redisServer;
    }    
    
    /**
     * @TODOKey操作
     * @param type $key
     * @param type $value
     * @return type
     */
    public function redis_set($key, $value){
        $redisCount = $this->redisCount;
        return $redisCount->set($key, $value);
    }
    
    public function redis_get($key){
        $redisCount = $this->redisCount;
        return $redisCount->get($key);
    }    
    
    public function redis_exists($key){
        $redisCount = $this->redisCount;
        return $redisCount->exists($key);
    }
    
    public function redis_del($key){
         $redisCount     = $this->redisCount;
         return $redisCount->del($key);
    }
    /**
     * @TODOHash操作
     * @param type $key
     * @param type $field
     * @return type
     *
     */
    public function redis_hSet($key,$field,$value){//为hash表中的字段赋值。成功返回1，失败返回0。若hash表不存在会先创建表再赋值，若字段已存在会覆盖旧值。
        $redisCount     = $this->redisCount;
        return $redisCount->hSet($key,$field,$value);
    }
    
    public function redis_hMset($key,$filedArray){//['key1'=>'filed','key2'=>'filed'] 同时设置某个hash表的多个字段值。成功返回true。
        $redisCount     = $this->redisCount;
        return $redisCount->hMset($key,$filedArray);
    }
    public function redis_hGet($key,$field){//获取hash表中指定字段的值。若hash表不存在则返回false。
        $redisCount     = $this->redisCount;
        $keyField       = $redisCount->hGet($key,$field);
        if($keyField == false){
            return $keyField;
        }else{
            return json_decode($keyField,true);
        }
    }
    public function redis_hMget($key,$filedArray){//['key1','key2']  同时获取某个hash表的多个字段值。其中不存在的字段值为false。
        $redisCount     = $this->redisCount;
        $keyField       = $redisCount->hMget($key,$filedArray);
        if($keyField == false){
            return $keyField;
        }else{
            return json_decode($keyField,true);
        }
    }
    public function redis_hExists($key,$field){//查看hash表的某个字段是否存在，存在返回true，否则返回false
        $redisCount     = $this->redisCount;
        return $redisCount->hExists($key,$field);
    }
    public function redis_hDel($key,$field){//删除hash表的一个字段，不支持删除多个字段。成功返回1，否则返回0。
        $redisCount     = $this->redisCount;
        return $redisCount->hDel($key,$field);
    }
    public function redis_hKeys($key){//获取某个hash表所有字段名。hash表不存在时返回空数组，key不为hash表时返回false。
        $redisCount     = $this->redisCount;
        return $redisCount->hKeys($key);
    }
    public function redis_hVals($key){//获取某个hash表所有字段名。hash表不存在时返回空数组，key不为hash表时返回false。
        $redisCount     = $this->redisCount;
        return $redisCount->hVals($key);
    }
    public function redis_hGetall($key){//获取在哈希表中指定 key 的所有字段和值，key不为hash表时返回false。
        $redisCount     = $this->redisCount;
        return $redisCount->hGetAll($key);
    }    

    /**
     * @TODO字符串操作
     */
    public function redis_Sset($key,$field){//  设置键值：成功返回true，否则返回false
        $redisCount = $this->redisCount;
        return $redisCount->set($key,$field);
    }
    public function redis_SsetRange($key,$offset,$field){//    从某个key所存储的字符串的指定偏移量开始，替换为另一指定字符串，成功返回替换后新字符串的长度。
        $redisCount = $this->redisCount;
        return $redisCount->setRange($key,$offset,$field);

    }
    public function redis_Sget($key){//获取键值：成功返回String类型键值，若key不存在或不是String类型则返回false
        $redisCount = $this->redisCount;
        return $redisCount->get($key);

    }
    public function redis_SgetRange($key,$str,$sonStr){//获取存储在指定key中字符串的子字符串。
        $redisCount = $this->redisCount;
        return $redisCount->getRange($key,$str,$sonStr);
    }

    public function redis_Sexpire($key,$invalidTime){//命令用于设置 key 的过期时间，key 过期后将不再可用。单位以秒计。
        $redisCount = $this->redisCount;
        return $redisCount->expire($key, $invalidTime);
    }    
    
    public function redis_Sexpireat($key,$invalidTime){//命令用于以 UNIX 时间戳(unix timestamp)格式设置 key 的过期时间。key 过期后将不再可用。
        $redisCount = $this->redisCount;
        return $redisCount->expireAt($key, $invalidTime);
    }
    
    /**
     * @TODO列表操作（ list ）
     */

    public function redis_lPush($key,$valueList=array()){//从list头部插入一个值,array->string
        $redisCount = $this->redisCount;
        $value = implode(" ", $valueList);
        return $redisCount->lPush($key,$value);
    }
    public function redis_lPushx($key,$valueList=array()){//将一个或多个值插入已存在的列表头部，列表不存在时操作无效
        $redisCount = $this->redisCount;
        $value = implode(" ", $valueList);
        return $redisCount->lPushx($key,$value);

    }

    public function redis_rPush($key,$value){//从list尾部插入一个值,array->string
        $redisCount = $this->redisCount;
        return $redisCount->rPush($key,$value);
    }
    public function redis_rPushx($key,$valueList=array()){//将一个或多个插入到列表的尾部，列表不存在时操作无效
        $redisCount = $this->redisCount;
        $value = implode(" ", $valueList);
        return $redisCount->rPushx($key,$value);

    }

    public function redis_lrange($key,$start,$end){//获取列表指定区间中的元素，，0标识列表的第一个元素，-1表示列表最后一个元素，-2表示倒数第2
        $redisCount = $this->redisCount;
        try{
            $lrange = $redisCount->lrange($key,$start,$end);
            return json_decode($lrange,true);
        } catch (Exception $e){
            return false;
        }
    }

    public function redis_blPop($key,int $time=5){//移除并返回列表的第一个元素，若key不存在或不是列表则返回false
        $redisCount = $this->redisCount;
        $lPop = $redisCount->blPop($key,$time);
        return $lPop;
    }
    public function redis_brPop($key,int $time=5){//移除并返回列表的最后一个元素，若key不存在或不是列表返回false
        $redisCount = $this->redisCount;
        return $redisCount->brPop($key,$time);
    }

    public function redis_lLen($key){//返回列表长度
        $redisCount = $this->redisCount;
        return $redisCount->lLen($key);
    }
    public function redis_ltrim($key,int $start,int $end){//对一个列表进行修剪，只保留区间元素，其它元素都删除，成功返回true
        $redisCount = $this->redisCount;
        return $redisCount->ltrim($key,$start,$end);
    }
}
