<?php
/**
 * Created by PhpStorm.
 * User: titan
 * Date: 02.03.17
 * Time: 21:16
 */

namespace titanproger\redis_models;


class TRedisModelHash extends  TRedisModelBase
{
    public function updateCounters($counters) {
        $key_name = $this->getRedisKeyName();
        foreach($counters as $name => $value) {
            static::getRedisDb()->cmdHashFieldIncrement($key_name, $name , $value );
            //duplicates \yii\db\BaseActiveRecord::updateCounters from here
            if (!isset($this->_attributes[$name]))
                $this->_attributes[$name] = $value;
            else
                $this->_attributes[$name] += $value;

            $this->_oldAttributes[$name] = $this->_attributes[$name];
        }
    }


    protected function saveToKey($key_name) {
        $result =  static::getRedisDb()->cmdHashSet( $key_name , $this->getAttributes(), $this->expire_time );
        return $result != null;
    }

    protected function loadFromKey($key_name) {
        $this->setAttributes( $data = static::getRedisDb()->cmdHashGet($key_name) , false);
        return $data != null;
    }
}