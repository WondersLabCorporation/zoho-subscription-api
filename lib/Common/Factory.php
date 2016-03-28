<?php

namespace Zoho\Subscription\Common;

use Zoho\Subscription\Client\Client;
use Zoho\Subscription\Common\Record;
use Zoho\Subscription\Common\SubscriptionException;

class Factory
{
    const DEFAULT_ENTITIES_NAMESPACE = 'Zoho\Subscription\Api\\';
    
    public static function createEntity($entity, $params = [], $client = null, $throw_exception = true)
    {
        if (empty($client)){
            $client = new Client($params);
        }
        if (empty($params['path'])){
            $params['path'] = static::DEFAULT_ENTITIES_NAMESPACE;
        }
        
        $classname = $params['path'] . $entity;
        if (!class_exists($classname)){
            if ($throw_exception){
                throw new SubscriptionException('No such entity found');
            } else {
                return null;
            }
        }
        return new $classname($client, $params);
    }
    
    /**
     * Create and load class with a given arguments
     * 
     * Class name
     * @param string $entity
     * @param array $params data to pass to entity constructor
     * @param Client|null $client
     * @param boolean $throw_exception
     * @return Record
     * @throws SubscriptionException
     */
    public static function getEntity($entity, $params = [], $client = null, $throw_exception = true)
    {
        if (empty($params['id'])) {
            throw new SubscriptionException('Subscription entity ID param is required');
        }
        $entityItem = static::createEntity($entity, $params, $client, $throw_exception);
        if (!$throw_exception && $entityItem === null){
            return null;
        }
        $entityItem->load($params['id']);
        return $entityItem;
    }
    
    /**
     * Return array of Entity objects.
     * 
     * Class name
     * @param string $entity
     * @param array $params data to pass to entity constructor
     * @param Client|null $client
     * @param boolean $throw_exception
     * @return Record[]
     * @throws SubscriptionException
     */
    public static function getEntityList($entity, $params = [], $client = null, $throw_exception = true)
    {
        $command = lcfirst($entity).'s';
        if (empty($client)){
            $client = new Client($params);
        }
        if (!isset($params['query'])){
            $params['query'] = [];
        }
        
        $entities_data = $client->getList($command, $params['query']);
        if ($client->hasError()){
            if ($throw_exception){
                throw new SubscriptionException($client->getError());
            } else {
                return null;
            }
        }
        return static::getEntitiesFromArray($entities_data, $entity, $client, $throw_exception);
    }
    
    /**
     * Build Entitiy objects form array
     * @param array $entities_data
     * @param string $entity
     * @param Client|null $client
     * @param boolean $throw_exception
     * @return Record[]
     * @throws SubscriptionException
     */
    public static function getEntitiesFromArray(array $entities_data, $entity = null, $client = null, $throw_exception = true)
    {
        $result = [];
        foreach ($entities_data as $data) {
            $entityItem = self::createEntity($entity, $data, $client, $throw_exception);
            $result[] = $entityItem;
        }
        return $result;
    }
}