<?php
/**
 * Created by PhpStorm.
 * User: titan
 * Date: 02.03.17
 * Time: 21:16
 */

namespace titanproger\redis_models;


class TRedisModelStringJson extends  TRedisModelBase
{
    protected function saveToKey($key_name) {
        $result =  static::getRedisDb()->cmdStringSet( $key_name , $this->saveToJson(), $this->expire_time);
        return $result != null;
    }

    protected function loadFromKey($key_name) {
        $data = static::getRedisDb()->cmdStringGet( $key_name );
        $this->loadFromJson( $data , false );
        return $data != null;
    }
}