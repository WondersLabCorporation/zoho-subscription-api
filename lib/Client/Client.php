<?php

namespace Zoho\Subscription\Client;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

abstract class Client implements \ArrayAccess
{
    /**
     * @var String
     */
    protected $token;

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
     * @param string                            $token
     * @param int                               $organizationId
     * @param \Doctrine\Common\Cache\Cache|null $cache
     * @param int                               $ttl
     */
    public function __construct($token, $organizationId, Cache $cache = null, $ttl = 7200)
    {
        $this->warnings = [];
        $this->token = $token;
        $this->organizationId = $organizationId;
        $this->ttl = $ttl;
        $this->cache = $cache;
        $this->client = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Zoho-authtoken '.$token,
                'X-com-zoho-subscriptions-organizationid' => $organizationId,
            ],
            'base_uri' => 'https://subscriptions.zoho.com/api/v1/',
        ]);
    }

    /**
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function processResponse(Response $response=null)
    {
        if ($response === null){
            $this->error = 'Zoho Api subscription error : null data in processResponse';
            return null;
        }
        if ($response->getStatusCode() > 201){
            $this->error = 'Zoho Api subscription error : '.$response->getReasonPhrase();
            return null;
        }
        $data = json_decode($response->getBody(), true);
        if ($data['code'] != 0) {
            $this->setError('Zoho Api subscription error : '.$data['message']);
            return null;
        }
        return $data;
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
        if ($this->cache and $this->cache->contains($key)) {
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
     */
    protected function deleteCacheByKey($key)
    {
        if ($this->cache === null){
            return true;
        }
        $this->cache->delete($key);
    }
    
    /**
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->error);
    }
    
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
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
    
    public function load($id = null)
    {
        if ($id !== null){
            $this->setId($id);
        }
        $response = $this->request('GET', $this->getCommandRetrieve(), [
                'content-type' => 'application/json',
            ]);
        $data = $this->processResponse($response);
        if ($this->hasError()){
            return null;
        }
        $this->container = $data;
        return $this;
    }
    
    public function save(array $template = null){
        $this->error = null;
        $data = $this->container[$this->module];
        $this->beforPrepareData($data);
        $data = $this->prepareData($data, $template);
        $response = $this->internalSave($data);
        if ($this->hasError()){
            return null;
        }
        $this->container = $response;
        return $this;
    }

    protected function internalsave(array $data)
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
    
    protected function beforPrepareData(&$data)
    {
        return;
    }
    
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
            } elseif (array_key_exists($value, $data)){
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
        return $this[$this->module.'_id'];
    }
    
    protected function setId($id)
    {
        $this[$this->module.'_id'] = $id;
    }
    
    abstract protected function getCreateTemplate();
    
    abstract protected function getUpdateTemplate();
    
    /**
     * Non exception wrapper for client->request
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
            $this->container[] = $value;
        } else {
            if (!isset($this->container[$this->module])){
                $this->container[$this->module] = [];
            }
            $this->container[$this->module][$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function &offsetGet($offset) {
        if (isset($this->container[$this->module], $this->container[$this->module][$offset])){
            return $this->container[$this->module][$offset];
        } elseif (isset($this->container[$offset])){
            return $this->container[$offset];
        } else {
            $null = null;
            return $null;
        }
    }
}
