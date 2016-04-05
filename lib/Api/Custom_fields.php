<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Custom_fields extends Record
{
    private $data = [];
    
    public function setAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
    
    /**
     * 
     * @param string $attribute
     * @param array|string $value
     */
    public function setAttribute($attribute, $value)
    {
        if (is_array($value)) {
            $this->setAttribute($value['index'], $value['value']);
            
        } elseif (is_numeric($attribute)) {
            $this->data[$attribute] = $value;
        }
    }
    
    public function getAttributes()
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            $data[] = ['index' => $key, 'value' => $value];
        }
        return $data;
    }
    
    /**
     * @param string $attribute Must be 'value_<index>', where index is number from 1 to 10.
     */
    public function __get($attribute)
    {
        $key = substr($attribute, 6);
        return $this->data[$key];
    }
    
    /**
     * @param string $attribute Must be 'value_<index>', where index is number from 1 to 10.
     * @param mixed $value 
     */
    public function __set($attribute, $value)
    {
        $key = substr($attribute, 6);
        $this->data[$key] = $value;
    }
}
