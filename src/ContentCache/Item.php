<?php

namespace InfusionWeb\Laravel\ContentCache;

class Item
{

    // Result object.
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->item->$name;
        }

        trigger_error("Undefined property: InfusionWeb\Laravel\ContentCache\Item::\$$name", E_USER_ERROR);
    }

    public function __set($name, $value)
    {
        return $this->item->$name = $value;
    }

    public function attributes()
    {
        return get_object_vars($this->item);
    }

    public function attributeKeys()
    {
        return array_keys($this->attributes());
    }

    public function hasAttribute($name)
    {
        $attributes = $this->attributes();

        return array_key_exists($name, $this->attributes());
    }

}
