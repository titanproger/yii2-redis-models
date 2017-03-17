<?php
/**
 * Created by PhpStorm.
 * User: titan
 * Date: 07.03.17
 * Time: 18:14
 */

namespace titanproger\redis_models;

use yii\redis\Connection;

class TRedisConnection extends Connection {

    public function cmdStringGet($key_name) {
        return $this->executeCommand("GET", [$key_name]);
    }
    public function cmdStringSet($key_name, $value, $expires = null) {
        if($expires == null)
            return $this->executeCommand("SET", [$key_name, $value]);
        else
            return $this->executeCommand("SETEX", [$key_name, $expires, $value]);
    }

    public function cmdKeySetExpireSeconds($key_name, $seconds) {
        return self::GetResultScalar($this->executeCommand("EXPIRE", [$key_name, $seconds]));
    }
    public function cmdKeyIsExist($key_name) {
        return self::GetResultScalar($this->executeCommand("EXISTS", [$key_name]));
    }
    public function cmdKeyLength($key_name) {
        return $this->executeCommand('STRLEN', [$key_name]);
    }
    public function cmdKeyDelete($key_name) {
        return $this->executeCommand('DEL', [ $key_name ]);
    }
    public function cmdKeyRename($key_name_old, $key_name_new) {
        return $this->executeCommand('RENAME', [$key_name_old, $key_name_new]);
    }

    public function cmdHashSet($key, $data, $expires = null ) {
        $ret =  $this->executeCommand('HMSET',  self::ArrayToRedis($data, [$key]));
        if($expires)
            $this->cmdKeySetExpireSeconds($key, $expires);
        return $ret;
    }
    public function cmdHashGet($key) {
        $data = $this->executeCommand('HGETALL', [$key]);
        if(!$data)
            return null;
        return  $this->ArrayFromRedis($data);
    }
    public function cmdHashFieldIncrement($key, $field, $delta) {
        return $this->executeCommand('HINCRBY', [$key, $field, $delta]);
    }

    public function cmdListPushFront($key, $value, $max_length = 0, $expires = null) {
        $this->LPUSH( $key , $value );
        if($max_length)
            $this->LTRIM( $key , 0, $max_length - 1);
        if($expires)
            $this->cmdKeySetExpireSeconds($key, $expires);
    }
    public function cmdListPushBack($key, $value, $max_length = 0, $expires = null) {
        $this->RPUSH( $key , $value );
        if($max_length)
            $this->LTRIM( $key , -$max_length, -1);
        if($expires)
            $this->cmdKeySetExpireSeconds($key, $expires);
    }
    public function cmdListGet($key, $from = 0, $to = -1) {
        return  $this->LRANGE($key, $from, $to);
    }


    private static function ArrayToRedis($data, $init_array = []) {
        $params = &$init_array;
        foreach($data as $key => $value) {
            $params[] = $key;
            $params[] = $value;
        }
        return $params;
    }
    private static function ArrayFromRedis($data) {
        $result = [];
        $key = null;
        foreach($data as $value ) {
            if($key === null) {
                $key = $value;
                continue;
            }
            $result[$key] = $value;
            $key = null;
        }
        return $result;
    }
    private static function GetResultScalar($result) {
        if(!isset($result[0]))
            return false;
        return $result[0];
    }
}