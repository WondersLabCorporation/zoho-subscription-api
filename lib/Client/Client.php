<?php

namespace Zoho\Subscription\Client;

use yii\base\InvalidConfigException;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use \Zoho\Subscription\Api\SubscriptionException;

class Client implements \ArrayAccess
{
    const DEFAULT_ENTITIES_NAMESPACE = 'Zoho\Subscription\Api\\';
    /**
     * @var String
     */
    protected $subscriptionsToken;

    /**
     * @var String
     */
    protected $organizationId;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $warnings;
    
    /**
     * @var array 
     */
    protected $container;

    /**
     * @var string module name
     */
    protected $module;

    protected $command;

    protected $base_template = [];
    
    protected $params;
    
    /**
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        if (empty($params['organizationId']) || empty($params['subscriptionsToken'])){
            throw new \Exception('Token and Organization ID are required');
        }
        $this->params = $params;
        $this->warnings = [];
        $this->subscriptionsToken = $params['subscriptionsToken'];
        $this->organizationId = $params['organizationId'];
        $this->ttl = isset($params['ttl']) ? $params['ttl'] : 7200;
        $this->cache = isset($params['cache']) ? $params['cache'] : null;
        $this->client = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Zoho-authtoken '.$this->subscriptionsToken,
                'X-com-zoho-subscriptions-organizationid' => $this->organizationId,
            ],
            'base_uri' => 'https://subscriptions.zoho.com/api/v1/',
        ]);
    }

    /**
     * @param Response $response
     * @throws SubscriptionException
     *
     * @return array
     */
    protected function processResponse(Response $response = null)
    {
        if ($this->error){
            throw new SubscriptionException($this->error);
        }
        if ($response === null) {
            throw new SubscriptionException('Zoho Api subscription error : null data in processResponse');
        }
        if ($response->getStatusCode() > 201) {
            throw new SubscriptionException('Zoho Api subscription error : '.$response->getReasonPhrase());
        }
        $data = json_decode($response->getBody(), true);
        if ($data['code'] != 0) {
            throw new SubscriptionException('Zoho Api subscription error : '.$data['message']);
        }
        return $data;
    }
    
    /**
     * @param Response $response
     * @return null
     */
    protected function processResponseAndSave(Response $response=null)
    {
        $data = $this->processResponse($response);
        $this->container = $data;
    }

    /**
     * @param $key
     *
     * @throws \LogicException
     *
     * @return bool|mixed
     */
    protected function getFromCache($key)
    {
        // If the results are already cached
        if ($this->cache !== null and $this->cache->contains($key)) {
            return unserialize($this->cache->fetch($key));
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $values
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function saveToCache($key, $values)
    {
        if ($this->cache === null){
            return true;
        }
        if (null === $key) {
            throw new \LogicException('If you want to save to cache, an unique key must be set');
        }

        return $this->cache->save($key, serialize($values), $this->ttl);
    }

    /**
     * @param string $key
     * 
     * @return boolean
     */
    protected function deleteCacheByKey($key)
    {
        if ($this->cache === null){
            return true;
        }
        return $this->cache->delete($key);
    }
    
    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }
    
    public function setUserDefinedData($data)
    {
        $this->container[$this->module] = $data;
    }
    
    /**
     * 
     * @param string|int $id
     * @return \Zoho\Subscription\Client\Client
     * @throws SubscriptionException
     */
    public function load($id = null)
    {
        if ($id !== null){
            $this->setId($id);
        }
        $response = $this->request('GET', $this->getCommandRetrieve(), [
                'content-type' => 'application/json',
            ]);
        $data = $this->processResponse($response);
        $this->container = $data;
        return $this;
    }
    
    /**
     * 
     * @param array $template
     * @return \Zoho\Subscription\Client\Client
     * @throws SubscriptionException
     */
    public function save(array $template = null)
    {
        $data = $this->container[$this->module];
        $this->beforeSave($data);
        $data = $this->prepareData($data, $template);
        $response = $this->internalSave($data);
        $this->container = $response;
        return $this;
    }

    protected function internalSave(array $data)
    {
        if ($this->getId() === null){
            $data = $this->prepareData($data, $this->getCreateTemplate());
            $response = $this->request('POST', $this->getCommandCreate(), [
                'content-type' => 'application/json',
                'body' => json_encode($data),
            ]);
        } else {
            $data = $this->prepareData($data, $this->getUpdateTemplate());
            $response = $this->request($this->getUpdateMethod(), $this->getCommandUpdate(), [
                'content-type' => 'application/json',
                'body' => json_encode($data),
            ]);
        }
        return $this->processResponse($response);
    }
    
    protected function beforeSave(&$data)
    {
        return;
    }
    
    /**
     * Return the only data that complies to template
     * 
     * @param array $data
     * @param array $template
     * @return array
     */
    protected function prepareData(array $data, array $template=null)
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
    
    /**
     * Return data of single Entity from Zoho
     * @param array $query
     * @param integer $page
     * @param string $command
     * @return array
     * @throws SubscriptionException
     */
    public function getListPage(array $query, $page = 0, $command = null)
    {
        $cacheKey = sprintf('zoho_%s_%s_%s', $this->command, implode('', array_keys($query)), $page);
        
        $hit = $this->getFromCache($cacheKey);
        
        if ($hit === false){
            if ($page > 0){
                $query['page'] = $page;
            }
            $command = empty($command) ? $this->command : $command;
            $response = $this->request('GET', $command, [
                'content-type' => 'application/json',
                'query' => $query,
            ]);
            
            $result = $this->processResponse($response);
            
            $this->saveToCache($cacheKey, $result);
            
            return $result;
        }
        
        return $hit;
    }
    
    /**
     * Returns list of all Entities from Zoho
     * @param array $query Custom query
     * @param string $command
     * @return array
     * @throws SubscriptionException
     */
    public function getList(array $query = [], $command = null){
        $page = 1;
        $result = [];
        do {
            $page_data = $this->getListPage($query, $page);
            
            $result = array_merge($result, $page_data[$this->command]);
            
            if ($page_data['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
        } while ($nextPage);
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
        return $this[$this->module.'_id'];
    }
    
    protected function setId($id)
    {
        $this[$this->module.'_id'] = $id;
    }
    
    protected function getCreateTemplate()
    {
        return $this->base_template;
    }
    
    protected function getUpdateTemplate()
    {
        return $this->base_template;
    }
    
    /**
     * Create and load class with a given arguments
     * 
     * Class name
     * @param string $entity
     * Args to extract into __cuonstruct method
     * @param array $params
     * @return Client
     * @throws SubscriptionException
     */
    public static function getEntity($entity, $params = [])
    {
        if (empty($params['id'])) {
            throw new SubscriptionException('Subscription entity ID param is required');
        }
        $entityItem = static::createEntity($entity, $params);
        $entityItem->error = [];
        $entityItem->load($params['id']);
        return $entityItem;
    }
    
    /**
     * Create a class with a given arguments.
     * 
     * Class name
     * @param string $entity
     * @param array $params data to pass to entity constructor
     * @return Client
     * @throws SubscriptionException
     */
    public static function createEntity($entity, $params = [])
    {
        if (empty($params['path'])){
            $params['path'] = self::DEFAULT_ENTITIES_NAMESPACE;
        }
        $classname = $params['path'] . $entity;
        if(!class_exists($classname)){
            throw new SubscriptionException('No such entity found');
        }
        return new $classname($params);
    }
    
    /**
     * Return array of Entity objects.
     * 
     * @param string $entity Class name
     * @param array $params data to pass to entity constructor
     * @return array
     * @throws SubscriptionException
     */
    public static function getEntityList($entity, $params = [])
    {
        // TODO: Find a better solution instead of creating new entity and use it.
       $entity = self::createEntity($entity, $params);
       $query = [];
       if (isset($params['customer_id'])) {
           $query = ['customer_id' => $params['customer_id']];
       }
       return $entity->getList($query);
    }
    
    /**
     * Build Entitiy objects form array
     * @param array $entities_data
     * @param string $entity_name If null then name will be taken from $module param.
     * @return array Array of Entities
     */
    protected function buildEntitiesFromArray(array $entities_data, $entity_name = null)
    {
        if (empty($entity_name)){
            $entity_name = ucfirst($this->module);
        }
        $result = [];
        foreach ($entities_data as $data) {
            $classname = $this->params['path'] . $entity_name;
            $entity = new $classname($this->params);
            $entity[] = $data;
            array_push($result, $entity);
        }
        return $result;
    }
    
    /**
     * Non exception wrapper for Guzzle client
     * 
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return Response
     */
    protected function request($method, $uri = null, array $options = [])
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch(\Exception $e){
            $this->error = $e->getMessage();
        }
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[$this->module] = $value;
        } else {
            if (!isset($this->container[$this->module])){
                $this->container[$this->module] = [];
            }
            $this->container[$this->module][$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]) or isset($this->container[$this->module][$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function &offsetGet($offset) {
        if (isset($this->container[$this->module]) and isset($this->container[$this->module][$offset])){
            return $this->container[$this->module][$offset];
        } elseif (isset($this->container[$offset])){
            return $this->container[$offset];
        } else {
            $null = null;
            return $null;
        }
    }
}
