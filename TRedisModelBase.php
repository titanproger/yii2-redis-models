<?php
/**
 * Created by PhpStorm.
 * User: titan
 * Date: 07.03.17
 * Time: 18:28
 */


namespace titanproger\redis_models;

use yii\redis\ActiveRecord;

class TRedisModelBase extends ActiveRecord {

    /** @var  $expire_time int | null  if set then record will expire after this count of seconds*/
    var $expire_time;


    /**
     * @return TRedisConnection
     */
    public static function getDb() { return \Yii::$app->get('redis'); }

    /**
     * @return TRedisConnection
     */
    public static function getRedisDb() { return static::getDb(); }


    public function setLifeTime($seconds) { $this->expire_time = $seconds; }

    public function refreshLifeTime() {
        static::getRedisDb()->cmdKeySetExpireSeconds( $this->getRedisKeyName(), $this->expire_time);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        $class_name = get_called_class();
        return str_replace("\\", ":", $class_name);
    }

    public function getRedisKeyName() {
        $key = static::tableName();
        $pk = $this->primaryKey();
        foreach( $pk as $key_name)
            $key .= ":".$key_name."_".$this->getAttribute($key_name);
        return $key;
    }


    /**
     * @param $key_name
     * @return TRedisModelHash|null
     */
    public static function FindByPk($pk) {

        $class_name = get_called_class();
        /** @var $object TRedisModelHash */
        $object = new $class_name();
        $object->setPrimaryKey($pk);

        if($object->loadFromDB() )
            return $object;

        return null; //not found
    }

    public function loadFromDB() {
        $success = $this->loadFromKey($this->getRedisKeyName());
        if($success)
            $this->afterFind();
        return $success;
    }


    public function save($runValidation = true, $attributeNames = null) {
        if ($runValidation && !$this->validate($attributeNames))
            return false;

        $insert = $this->getIsNewRecord();
        if(!$this->beforeSave($insert))
            return false;
        $result = $this->saveToKey($this->getRedisKeyName());
        if($result)
            $this->afterSave($insert, []);
        return $result;
    }


    public function setPrimaryKey($pk) {
        $pk_name = $this->primaryKey();

        if(!is_array($pk))
            $pk = [$pk_name[0] => $pk];

        if(count($pk_name) != count($pk))
            throw new \Exception("RedisBase model - bad primary key length");

        foreach($pk_name as $pk_field_name)
            $this->$pk_field_name = $pk[$pk_field_name];
    }

    protected function saveToKey($key_name) { return false; }
    protected function loadFromKey($key_name) { return false;}

//    public function loadFromStringKey( $key_name ) {
//        $this->loadFromJson(static::getRedisDb()->cmdStringGet($key_name));
//    }
//    public function saveToStringKey( $key_name ) {
//        return static::getRedisDb()->cmdStringSet( $key_name , $this->saveToJson(), $this->expire_time );
//    }
//
    protected function loadFromJson($json_string, $safe_only = true) {
        if(!$json_string)
            return;
        $data = json_decode($json_string, true);
        $this->setAttributes( $data, $safe_only);
    }
    protected function saveToJson() {
        return json_encode($this->getAttributes());
    }



}