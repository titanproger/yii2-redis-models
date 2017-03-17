<?php
/**
 * Created by PhpStorm.
 * User: titan
 * Date: 07.03.17
 * Time: 18:33
 */

namespace titanproger\redis_models;


class TRedisModelList extends  TRedisModelBase {

    var $max_length = 0; // 0 - not limited


    var $items = [];
    var $items_encoded = [];

    public function setMaxLength($len) { $this->max_length = $len; }

    /**
     * @param $record TRedisModelList
     */
    public static function PushFront($record) {
        $record->getRedisDb()->cmdListPushFront(
            $record->getRedisKeyName() ,
            $record->saveToJson(),
            $record->max_length,
            $record->expire_time
        );
    }


    /**
     * @param $record TRedisModelList
     */
    public static function PushBack($record) {
        $record->getRedisDb()->cmdListPushBack(
            $record->getRedisKeyName() ,
            $record->saveToJson(),
            $record->max_length,
            $record->expire_time
        );
    }


    protected function loadFromKey($key_name) {
        $result = static::getRedisDb()->cmdListGet($key_name);
        if(!$result)
            return false;

        $this->items_encoded = $result;
        return true;
    }

    public function getItemsJson() { return $this->items_encoded; }

    public function getItems() {
        if(!empty($this->items))
            return $this->items;

        if(empty($this->items_encoded))
            return $this->items;

        $this->items = [];
        $class_name = get_called_class();
        foreach($this->items_encoded as $item_enc) {
            /** @var  $rec TRedisModelList */
            $rec  = new $class_name();
            $rec->loadFromJson($item_enc, false);

            $this->items[] = $rec;
        }
        return $this->items;
    }
}