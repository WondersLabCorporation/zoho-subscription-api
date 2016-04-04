<?php

namespace Zoho\Subscription\Common;

use Zoho\Subscription\Client\Client;
use Zoho\Subscription\Common\Factory;
use Zoho\Subscription\Common\SubscriptionException;

class Record
{
     /**
     * @var Client
     */
    protected $client;

     /**
     * @var string module name
     */
    protected $module;
    
     /**
     * @var string command name
     */
    protected $command;
    
     /**
     * @var array Common part of array for filtering data before save()
     */
    protected $base_template = [];
    
    /**
     * @param Client $client
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct($client, $attributes)
    {
        $this->client = $client;
        $this->setAttributes($attributes);
    }

     /**
     * Load Zoho record by ID
     * @param string|int $id
     * @return boolean
     */
    public function load($id = null)
    {
        if ($id !== null){
            $this->setId($id);
        }
        $response = $this->client->getRecord($this->getCommandRetrieve());
        return $this->processResponse($response);
    }

    /**
     * 
     * @param array $template
     * @return \Zoho\Subscription\Client\Client
     * @throws SubscriptionException
     */
    public function save(array $template = null)
    {
        $data = $this->getAttributes();
        $this->beforeSave($data);
        $filtered_data = $this->prepareData($data, $template);
        $response = $this->internalSave($filtered_data);
        return $this->processResponse($response);
    }

    protected function internalSave(array $data)
    {
        if (empty($this->getId())){
            $data = $this->prepareData($data, $this->getCreateTemplate());
            return $this->client->saveRecord('POST', $this->getCommandCreate(), $data);
        } else {
            $data = $this->prepareData($data, $this->getUpdateTemplate());
            return $this->client->saveRecord($this->getUpdateMethod(), $this->getCommandUpdate(), $data);
        }
    }
    
    protected function processResponse($response)
    {
        if ($this->hasError()){
            return false;
        }
        $this->setAttributes($response[$this->module]);
        return true;
    }
    
    protected function beforeSave(array &$data)
    {
        return;
    }
    
    /**
     * Return the only data that complies to template
     * 
     * @param array $data
     * @param array|null $template
     * @return array
     */
    protected function prepareData(array $data, $template = null)
    {
        if ($template === null){
            return $data;
        }
        $result = [];
        foreach ($template as $key => $value){
            if (is_array($value)){
                if (array_key_exists($key, $data)){
                    $result[$key] = $this->prepareData($data[$key], $value);
                } elseif ($key == '*'){
                    foreach ($data as $rowKey => $rowValue) {
                        $result[$rowKey] = $this->prepareData($rowValue, $value);
                    }
                }
            } elseif (array_key_exists($value, $data) && !empty($data[$value])){
                $result[$value] = $data[$value];
            }
        }
        return $result;
    }
    
    protected function getUpdateMethod()
    {
        return 'PUT';
    }
    
    protected function getCommandCreate()
    {
        return $this->command;
    }
    
    protected function getCommandUpdate()
    {
        return $this->command.'/'.$this->getId();
    }
    
    protected function getCommandRetrieve()
    {
        return $this->command.'/'.$this->getId();
    }
    
    protected function getId()
    {
        $param = $this->module.'_id';
        return $this->$param;
    }
    
    protected function setId($id)
    {
        $param = $this->module.'_id';
        $this->$param = $id;
    }
    
    protected function getCreateTemplate()
    {
        return $this->base_template;
    }
    
    protected function getUpdateTemplate()
    {
        return $this->base_template;
    }

    public function hasError()
    {
        return $this->client->hasError();
    }
    
    public function getError()
    {
        return $this->client->getError();
    }
    
    public function setAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
    
    public function setAttribute($attribute, $value)
    {
        if (!property_exists($this, $attribute)){
            return null;
        }
        if (is_array($value) && $entity = Factory::createEntity(ucfirst($attribute), $value, $this->client, false)){
            $this->$attribute = $entity;
        } else {
            $this->$attribute = $value;
        }  
    }

    public function getAttributes()
    {
        // TODO: must be a better way to get non-static public properties
        $all_public_properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $static_public_properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_STATIC);
        $public_properties = array_diff($all_public_properties, $static_public_properties);
        $data = [];
        // TODO: change to parse all public attributes
        foreach ($public_properties as $property){
            $value = $this->getAttribute($property->name);
            if (!empty($value)){
                $data[$property->name] = $value;
            }
        }
        return $data;
    }
    
    public function getAttribute($attribute)
    {
        if (!property_exists($this, $attribute)){
            return null;
        }
        if (is_object($this->$attribute) && method_exists($this->$attribute, 'getAttributes')){
            return $this->$attribute->getAttributes();
        } else {
            return $this->$attribute;
        }
    }
}
